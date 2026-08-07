<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'

const props = defineProps({
  rows: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const { formatMoney } = useMoney()

const year = ref(props.filters.year ?? new Date().getFullYear())

function applyFilters() {
  router.get('/finance/reports/monthly-revenue', { year: year.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

watch(year, applyFilters)

const monthNames = computed(() => Array.from({ length: 12 }, (_, index) =>
  new Date(2000, index, 1).toLocaleString(undefined, { month: 'long' })))

const total = computed(() => props.rows.reduce((sum, row) => sum + Number(row.total ?? 0), 0))

const peak = computed(() => props.rows.reduce((max, row) => Math.max(max, Number(row.total ?? 0)), 0))

const months = computed(() => {
  const byMonth = new Map(props.rows.map(row => [Number(row.month), row]))

  return monthNames.value.map((name, index) => {
    const row = byMonth.get(index + 1)
    const amount = Number(row?.total ?? 0)

    return {
      month: index + 1,
      name,
      amount,
      count: Number(row?.payments_count ?? 0),
      share: peak.value > 0 ? Math.round((amount / peak.value) * 100) : 0,
    }
  })
})
</script>

<template>
  <Head :title="t('finance.reports.monthlyRevenue')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.reports.monthlyRevenue') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.reports.monthlyRevenueDesc') }}</VCardSubtitle>

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
        v-model="year"
        type="number"
        :label="t('finance.fields.year')"
        density="compact"
        style="max-inline-size: 160px;"
      />

      <VSpacer />

      <VChip color="primary" variant="tonal" size="large">
        {{ t('finance.yearTotal') }}: {{ formatMoney(total) }}
      </VChip>
    </VCardText>

    <VDivider />

    <VCardText>
      <div
        v-for="month in months"
        :key="month.month"
        class="mb-4"
      >
        <div class="d-flex justify-space-between mb-1">
          <span class="text-body-2">{{ month.name }}</span>
          <span class="text-body-2">
            <strong>{{ formatMoney(month.amount) }}</strong>
            <span class="text-medium-emphasis"> · {{ month.count }}</span>
          </span>
        </div>
        <VProgressLinear
          :model-value="month.share"
          color="primary"
          height="8"
          rounded
        />
      </div>
    </VCardText>
  </VCard>
</template>
