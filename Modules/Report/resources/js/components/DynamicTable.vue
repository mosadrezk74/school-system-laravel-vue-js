<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  columns: { type: Array, default: () => [] },
  rows: { type: Array, default: () => [] },
  summary: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})

const { t } = useI18n()
const page = usePage()

const locale = computed(() => page.props.locale ?? 'en')

const headers = computed(() => props.columns.map(column => ({
  title: column.label,
  key: column.key,
  align: ['number', 'money', 'percentage'].includes(column.type) ? 'end' : 'start',
})))

const summaryEntries = computed(() => Object.entries(props.summary).map(([key, value]) => ({
  key,
  label: t(`report.summary.${key}`, key),
  value,
})))

function formatCell(column, value) {
  if (value === null || value === undefined || value === '')
    return '—'

  if (column.type === 'money') {
    return new Intl.NumberFormat(locale.value, {
      style: 'currency',
      currency: 'SAR',
      minimumFractionDigits: 2,
    }).format(Number(value))
  }

  if (column.type === 'percentage')
    return `${Number(value)}%`

  if (column.type === 'number')
    return new Intl.NumberFormat(locale.value).format(Number(value))

  if (column.type === 'date')
    return new Date(value).toLocaleDateString(locale.value)

  if (column.type === 'datetime')
    return new Date(value).toLocaleString(locale.value)

  return value
}

function isBadge(column) {
  return column.type === 'badge'
}
</script>

<template>
  <div>
    <VCardText v-if="summaryEntries.length" class="d-flex flex-wrap gap-3">
      <VChip
        v-for="entry in summaryEntries"
        :key="entry.key"
        variant="tonal"
        color="primary"
      >
        {{ entry.label }}: <strong class="ms-1">{{ entry.value }}</strong>
      </VChip>
    </VCardText>

    <VDivider v-if="summaryEntries.length" />

    <VDataTable
      :headers="headers"
      :items="rows"
      :loading="loading"
      :items-per-page="-1"
      hide-default-footer
    >
      <template
        v-for="column in columns"
        :key="column.key"
        #[`item.${column.key}`]="{ item }"
      >
        <VChip
          v-if="isBadge(column)"
          size="small"
          variant="tonal"
        >
          {{ item[column.key] ?? '—' }}
        </VChip>
        <span v-else>{{ formatCell(column, item[column.key]) }}</span>
      </template>

      <template #no-data>
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-bar-chart-alt-2" size="40" class="mb-2" />
          {{ t('report.noRows') }}
        </div>
      </template>
    </VDataTable>
  </div>
</template>
