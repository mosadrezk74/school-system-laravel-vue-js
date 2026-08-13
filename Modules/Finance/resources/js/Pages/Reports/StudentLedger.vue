<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  rows: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const { formatMoney, formatDate } = useMoney()

/*
 * These parameters define the report rather than narrow a list, but the URL
 * state, debounce and paging behaviour are the same — so they come from the
 * shared composable instead of a per-page copy.
 */
const { filters, goToPage } = useIndexFilters({
  url: '/finance/reports/student-ledger',
  initial: {
    student_id: props.filters.student_id ?? null,
    date_from: props.filters.date_from ?? null,
    date_to: props.filters.date_to ?? null,
  },
})

const items = computed(() => props.rows.data ?? [])

const totals = computed(() => items.value.reduce((acc, row) => ({
  billed: acc.billed + Number(row.total_amount ?? 0),
  paid: acc.paid + Number(row.paid_amount ?? 0),
  balance: acc.balance + Number(row.balance_due ?? 0),
}), { billed: 0, paid: 0, balance: 0 }))

const statusColors = {
  pending: 'warning',
  partial: 'info',
  paid: 'success',
  cancelled: 'secondary',
  overdue: 'error',
}

const headers = computed(() => [
  { title: t('finance.fields.invoice_number'), key: 'invoice_number' },
  { title: t('finance.fields.issue_date'), key: 'issue_date' },
  { title: t('finance.fields.due_date'), key: 'due_date' },
  { title: t('finance.fields.status'), key: 'status' },
  { title: t('finance.fields.total_amount'), key: 'total_amount' },
  { title: t('finance.fields.paid_amount'), key: 'paid_amount' },
  { title: t('finance.fields.balance_due'), key: 'balance_due', align: 'end' },
])
</script>

<template>
  <Head :title="t('finance.reports.studentLedger')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.reports.studentLedger') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.reports.studentLedgerDesc') }}</VCardSubtitle>

      <template #append>
        <VBtn
          variant="tonal"
          color="secondary"
          prepend-icon="bx-arrow-back"
          href="/finance/dashboard"
          :as="Link"
        >
          {{ t('finance.backToDashboard') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="filters.student_id.value"
        inputmode="numeric"
        type="number"
        :label="t('finance.studentIdFilter')"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <AppDateField
        v-model="filters.date_from.value"
        :label="t('finance.dateFrom')"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <AppDateField
        v-model="filters.date_to.value"
        :label="t('finance.dateTo')"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-3">
      <VChip color="info" variant="tonal">
        {{ t('finance.totalBilled') }}: {{ formatMoney(totals.billed) }}
      </VChip>
      <VChip color="success" variant="tonal">
        {{ t('finance.totalPaidLabel') }}: {{ formatMoney(totals.paid) }}
      </VChip>
      <VChip color="warning" variant="tonal">
        {{ t('finance.totalOutstanding') }}: {{ formatMoney(totals.balance) }}
      </VChip>
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="items"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.invoice_number="{ item }">
        <Link :href="`/finance/invoices/${item.id}`">
          {{ item.invoice_number }}
        </Link>
      </template>

      <template #item.issue_date="{ item }">
        {{ formatDate(item.issue_date) }}
      </template>

      <template #item.due_date="{ item }">
        {{ formatDate(item.due_date) }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="statusColors[item.status] ?? 'secondary'"
          size="small"
          variant="tonal"
        >
          {{ item.status_label ?? '—' }}
        </VChip>
      </template>

      <template #item.total_amount="{ item }">
        {{ formatMoney(item.total_amount, item.currency) }}
      </template>

      <template #item.paid_amount="{ item }">
        {{ formatMoney(item.paid_amount, item.currency) }}
      </template>

      <template #item.balance_due="{ item }">
        <strong>{{ formatMoney(item.balance_due, item.currency) }}</strong>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-book-content"
          :title="t('finance.reports.empty')"
        />
      </template>
    </VDataTable>
  </VCard>
</template>
