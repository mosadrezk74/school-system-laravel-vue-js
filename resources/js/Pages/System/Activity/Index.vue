<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  activities: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /**
   * The academic year and semester the log was read under, resolved
   * server-side. Shown as a caption so an entry can be placed in the school
   * calendar — activity_log itself stores no academic columns.
   */
  context: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')
const eventFilter = ref(props.filters.event ?? null)

let searchTimer
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 350)
})

watch(eventFilter, () => applyFilters())

// Cleared filters are omitted rather than sent empty, matching the other
// index pages so the backend never reads '' as an explicit filter.
function buildQuery(extra = {}) {
  const query = { ...extra }

  if (search.value)
    query.search = search.value

  if (eventFilter.value)
    query.event = eventFilter.value

  return query
}

function applyFilters() {
  router.get('/system/activity', buildQuery(), { preserveState: true, replace: true })
}

function goToPage(page) {
  router.get('/system/activity', buildQuery({ page }), { preserveState: true })
}

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
      <VRow>
        <VCol cols="12" md="5">
          <VTextField
            v-model="search"
            maxlength="100"
            :label="t('systemActivity.search')"
            prepend-inner-icon="bx-search"
            clearable
          />
        </VCol>

        <VCol cols="12" md="3">
          <VSelect
            v-model="eventFilter"
            :items="eventOptions"
            :label="t('systemActivity.fields.event')"
            clearable
          />
        </VCol>
      </VRow>
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
