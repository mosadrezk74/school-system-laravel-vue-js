<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  reports: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/system/errors',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})

const statusOptions = computed(() =>
  props.statuses.map(option => ({ title: option.label, value: option.value }))
)

const headers = computed(() => [
  { title: t('systemErrors.fields.status'), key: 'status', sortable: false },
  { title: t('systemErrors.fields.error'), key: 'error', sortable: false },
  { title: t('systemErrors.fields.route'), key: 'route', sortable: false },
  { title: t('systemErrors.fields.method'), key: 'method', sortable: false },
  { title: t('systemErrors.fields.user'), key: 'user', sortable: false },
  { title: t('systemErrors.occurrences'), key: 'occurrences', sortable: false },
  { title: t('systemErrors.fields.occurredAt'), key: 'last_seen_at', sortable: false },
])

const formatDate = value => value ? new Date(value).toLocaleString() : '—'
</script>

<template>
  <Head :title="t('systemErrors.title')" />

  <AppPageHeader
    :title="t('systemErrors.title')"
    :subtitle="t('systemErrors.subtitle')"
  />

  <AppDataTable
    :headers="headers"
    :items="reports.data"
    mobile-title-key="message"
    :meta="reports.meta"
    empty-icon="bx-bug"
    :empty-title="t('systemErrors.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('systemErrors.search')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <AppFilterBar
        :definitions="filterDefinitions"
        :filters="filters"
        :active-keys="activeKeys"
        @clear="clear"
        @clear-all="reset"
      />
    </template>

    <template #item.status="{ item }">
      <VChip :color="item.status_color" size="small">
        {{ item.status_label }}
      </VChip>
    </template>

    <template #item.error="{ item }">
      <AppTableLink :href="`/system/errors/${item.id}`">
        {{ item.exception_short }}
      </AppTableLink>
      <div class="text-caption text-medium-emphasis text-truncate error-message">
        {{ item.message }}
      </div>
    </template>

    <template #item.route="{ item }">
      {{ item.route || '—' }}
    </template>

    <template #item.method="{ item }">
      <VChip size="x-small" variant="tonal">
        {{ item.method || '—' }}
      </VChip>
    </template>

    <template #item.user="{ item }">
      {{ item.user?.name ?? t('systemErrors.guest') }}
    </template>

    <template #item.occurrences="{ item }">
      {{ item.occurrences }}
    </template>

    <template #item.last_seen_at="{ item }">
      {{ formatDate(item.last_seen_at) }}
    </template>
  </AppDataTable>
</template>

<style scoped>
.error-message {
  max-inline-size: 22rem;
}
</style>
