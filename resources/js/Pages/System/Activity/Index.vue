<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  activities: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },

  /**
   * The academic year and semester the log was read under, resolved
   * server-side. Shown as a caption so an entry can be placed in the school
   * calendar — activity_log itself stores no academic columns.
   */
  context: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/system/activity',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})

const eventOptions = computed(() => [
  { title: t('systemActivity.events.created'), value: 'created' },
  { title: t('systemActivity.events.updated'), value: 'updated' },
  { title: t('systemActivity.events.deleted'), value: 'deleted' },
])

const headers = computed(() => [
  { title: t('systemActivity.fields.description'), key: 'description', sortable: false },
  { title: t('systemActivity.fields.event'), key: 'event', sortable: false },
  { title: t('systemActivity.fields.subject'), key: 'subject', sortable: false },
  { title: t('systemActivity.fields.causer'), key: 'causer', sortable: false },
  { title: t('systemActivity.fields.log'), key: 'log_name', sortable: false },
  { title: t('systemActivity.fields.occurredAt'), key: 'created_at', sortable: false },
])

const hasContext = computed(() => Boolean(props.context?.academic_year_id))

const formatDate = value => value ? new Date(value).toLocaleString() : '—'

const eventColor = event => ({
  created: 'success',
  updated: 'info',
  deleted: 'error',
}[event] ?? 'secondary')
</script>

<template>
  <Head :title="t('systemActivity.title')" />

  <AppPageHeader
    :title="t('systemActivity.title')"
    :subtitle="t('systemActivity.subtitle')"
  />

  <VAlert
    v-if="hasContext"
    type="info"
    variant="tonal"
    density="compact"
    class="mb-4"
  >
    {{ t('systemActivity.context', {
      year: context.academic_year_id,
      semester: context.semester_id ?? t('systemActivity.noSemester'),
    }) }}
  </VAlert>

  <AppDataTable
    :headers="headers"
    :items="activities.data"
    mobile-title-key="description"
    :meta="activities.meta"
    empty-icon="bx-history"
    :empty-title="t('systemActivity.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('systemActivity.search')"
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

    <template #item.description="{ item }">
      <AppTableLink :href="`/system/activity/${item.id}`">
        {{ item.description }}
      </AppTableLink>
    </template>

    <template #item.event="{ item }">
      <VChip
        v-if="item.event"
        :color="eventColor(item.event)"
        size="small"
      >
        {{ item.event }}
      </VChip>
      <span v-else>—</span>
    </template>

    <template #item.subject="{ item }">
      <template v-if="item.subject_type_short">
        {{ item.subject_type_short }}<span class="text-medium-emphasis"> #{{ item.subject_id }}</span>
      </template>
      <span v-else>—</span>
    </template>

    <template #item.causer="{ item }">
      {{ item.causer?.name ?? t('systemActivity.system') }}
    </template>

    <template #item.log_name="{ item }">
      <VChip size="x-small" variant="tonal">
        {{ item.log_name || '—' }}
      </VChip>
    </template>

    <template #item.created_at="{ item }">
      {{ formatDate(item.created_at) }}
    </template>
  </AppDataTable>
</template>
