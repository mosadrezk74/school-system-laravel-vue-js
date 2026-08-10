<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  rows: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const { formatMoney, formatDateTime } = useMoney()

const today = new Date().toISOString().slice(0, 10)

const dateFrom = ref(props.filters.date_from ?? today)
const dateTo = ref(props.filters.date_to ?? today)

function applyFilters() {
  const query = {}

  if (dateFrom.value)
    query.date_from = dateFrom.value

  if (dateTo.value)
    query.date_to = dateTo.value

  router.get('/finance/reports/daily-payments', query, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

watch([dateFrom, dateTo], applyFilters)

const items = computed(() => props.rows.data ?? [])

const total = computed(() => items.value.reduce((sum, row) => sum + Number(row.amount ?? 0), 0))

const byMethod = computed(() => {
  const groups = new Map()

  items.value.forEach(row => {
    const label = row.payment_method_label ?? '—'

    groups.set(label, (groups.get(label) ?? 0) + Number(row.amount ?? 0))
  })

  return Array.from(groups, ([label, amount]) => ({ label, amount }))
})

const headers = computed(() => [
  { title: t('finance.fields.paid_at'), key: 'paid_at' },
  { title: t('finance.fields.student_id'), key: 'student' },
  { title: t('finance.fields.invoice_id'), key: 'invoice' },
  { title: t('finance.fields.payment_method'), key: 'payment_method' },
  { title: t('finance.fields.reference_number'), key: 'reference_number' },
  { title: t('finance.fields.amount'), key: 'amount', align: 'end' },
])
</script>

<template>
  <Head :title="t('finance.reports.dailyPayments')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.reports.dailyPayments') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.reports.dailyPaymentsDesc') }}</VCardSubtitle>

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
      <AppDateField
        v-model="dateFrom"
        :label="t('finance.dateFrom')"
        density="compact"
        style="max-inline-size: 200px;"
      />

      <AppDateField
        v-model="dateTo"
        :label="t('finance.dateTo')"
        density="compact"
        style="max-inline-size: 200px;"
      />

      <VSpacer />

      <VChip color="success" variant="tonal" size="large">
        {{ t('finance.totalPaidLabel') }}: {{ formatMoney(total) }}
      </VChip>
    </VCardText>

    <template v-if="byMethod.length">
      <VDivider />
      <VCardText class="d-flex flex-wrap gap-3">
        <VChip
          v-for="group in byMethod"
          :key="group.label"
          variant="tonal"
        >
          {{ group.label }}: {{ formatMoney(group.amount) }}
        </VChip>
      </VCardText>
    </template>

    <VDataTable
      :headers="headers"
      :items="items"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.paid_at="{ item }">
        {{ formatDateTime(item.paid_at) }}
      </template>

      <template #item.student="{ item }">
        {{ item.student?.name ?? '—' }}
      </template>

      <template #item.invoice="{ item }">
        <Link
          v-if="item.invoice"
          :href="`/finance/invoices/${item.invoice_id}`"
        >
          {{ item.invoice.invoice_number }}
        </Link>
        <span v-else>—</span>
      </template>

      <template #item.payment_method="{ item }">
        <VChip size="small" variant="tonal">
          {{ item.payment_method_label ?? '—' }}
        </VChip>
      </template>

      <template #item.reference_number="{ item }">
        {{ item.reference_number ?? '—' }}
      </template>

      <template #item.amount="{ item }">
        <strong>{{ formatMoney(item.amount) }}</strong>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-calendar-check"
          :title="t('finance.reports.empty')"
        />
      </template>
    </VDataTable>
  </VCard>
</template>
