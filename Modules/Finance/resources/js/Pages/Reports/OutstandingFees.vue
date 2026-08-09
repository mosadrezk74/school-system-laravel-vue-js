<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppEmptyState from '@/components/AppEmptyState.vue'

const props = defineProps({
  rows: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const { formatMoney, formatDate } = useMoney()

const dateFrom = ref(props.filters.date_from ?? '')
const dateTo = ref(props.filters.date_to ?? '')

function applyFilters() {
  const query = {}

  if (dateFrom.value)
    query.date_from = dateFrom.value

  if (dateTo.value)
    query.date_to = dateTo.value

  router.get('/finance/reports/outstanding-fees', query, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

watch([dateFrom, dateTo], applyFilters)

const items = computed(() => props.rows.data ?? [])

const totalOutstanding = computed(() => items.value
  .reduce((sum, row) => sum + Number(row.balance_due ?? 0), 0))

const headers = computed(() => [
  { title: t('finance.fields.invoice_number'), key: 'invoice_number' },
  { title: t('finance.fields.student_id'), key: 'student' },
  { title: t('finance.fields.due_date'), key: 'due_date' },
  { title: t('finance.fields.total_amount'), key: 'total_amount' },
  { title: t('finance.fields.paid_amount'), key: 'paid_amount' },
  { title: t('finance.fields.balance_due'), key: 'balance_due', align: 'end' },
])
</script>

<template>
  <Head :title="t('finance.reports.outstandingFees')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.reports.outstandingFees') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.reports.outstandingFeesDesc') }}</VCardSubtitle>

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

    <VCardText class="d-flex flex-wrap gap-4 align-center">
      <VTextField
        v-model="dateFrom"
        type="date"
        :label="t('finance.dateFrom')"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <VTextField
        v-model="dateTo"
        type="date"
        :label="t('finance.dateTo')"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <VSpacer />

      <VChip color="warning" variant="tonal" size="large">
        {{ t('finance.totalOutstanding') }}: {{ formatMoney(totalOutstanding) }}
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

      <template #item.student="{ item }">
        {{ item.student?.name ?? '—' }}
      </template>

      <template #item.due_date="{ item }">
        {{ formatDate(item.due_date) }}
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
          icon="bx-check-circle"
          :title="t('finance.reports.empty')"
        />
      </template>
    </VDataTable>
  </VCard>
</template>
