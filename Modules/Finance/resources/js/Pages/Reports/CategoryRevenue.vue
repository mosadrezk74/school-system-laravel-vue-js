<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  rows: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const { formatMoney } = useMoney()
const page = usePage()

const dateFrom = ref(props.filters.date_from ?? '')
const dateTo = ref(props.filters.date_to ?? '')

function applyFilters() {
  const query = {}

  if (dateFrom.value)
    query.date_from = dateFrom.value

  if (dateTo.value)
    query.date_to = dateTo.value

  router.get('/finance/reports/category-revenue', query, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

watch([dateFrom, dateTo], applyFilters)

/*
 * This report aggregates with a raw join, so the translatable category name
 * arrives as the stored JSON rather than a resolved string — pick the active
 * locale here, falling back to the first translation present.
 */
function categoryName(value) {
  if (!value)
    return '—'

  let translations = value

  if (typeof value === 'string') {
    try {
      translations = JSON.parse(value)
    } catch {
      return value
    }
  }

  if (typeof translations !== 'object')
    return String(translations)

  const locale = page.props.locale ?? 'en'

  return translations[locale] ?? Object.values(translations)[0] ?? '—'
}

const total = computed(() => props.rows.reduce((sum, row) => sum + Number(row.total ?? 0), 0))

const items = computed(() => props.rows.map(row => ({
  ...row,
  name: categoryName(row.fee_category_name),
  share: total.value > 0 ? Math.round((Number(row.total ?? 0) / total.value) * 100) : 0,
})))

const headers = computed(() => [
  { title: t('finance.fields.fee_category_id'), key: 'name' },
  { title: t('finance.itemsCount'), key: 'items_count' },
  { title: t('finance.share'), key: 'share' },
  { title: t('finance.fields.total_amount'), key: 'total', align: 'end' },
])
</script>

<template>
  <Head :title="t('finance.reports.categoryRevenue')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.reports.categoryRevenue') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.reports.categoryRevenueDesc') }}</VCardSubtitle>

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
        clearable
        style="max-inline-size: 200px;"
      />

      <AppDateField
        v-model="dateTo"
        :label="t('finance.dateTo')"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <VSpacer />

      <VChip color="primary" variant="tonal" size="large">
        {{ t('finance.fields.total_amount') }}: {{ formatMoney(total) }}
      </VChip>
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="items"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.share="{ item }">
        <div class="d-flex align-center gap-2" style="min-inline-size: 140px;">
          <VProgressLinear
            :model-value="item.share"
            color="primary"
            height="6"
            rounded
          />
          <span class="text-caption">{{ item.share }}%</span>
        </div>
      </template>

      <template #item.total="{ item }">
        <strong>{{ formatMoney(item.total) }}</strong>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-pie-chart-alt-2"
          :title="t('finance.reports.empty')"
        />
      </template>
    </VDataTable>
  </VCard>
</template>
