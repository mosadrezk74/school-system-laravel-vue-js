<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  reports: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')
const statusFilter = ref(props.filters.status ?? null)

let searchTimer
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 350)
})

watch(statusFilter, () => applyFilters())

// Cleared filters are omitted rather than sent empty, matching the other
// index pages so the backend never reads '' as an explicit filter.
function buildQuery(extra = {}) {
  const query = { ...extra }

  if (search.value)
    query.search = search.value

  if (statusFilter.value)
    query.status = statusFilter.value

  return query
}

function applyFilters() {
  router.get('/system/errors', buildQuery(), { preserveState: true, replace: true })
}

function goToPage(page) {
  router.get('/system/errors', buildQuery({ page }), { preserveState: true })
}

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
      <VRow>
        <VCol cols="12" md="5">
          <VTextField
            v-model="search"
            maxlength="100"
            :label="t('systemErrors.search')"
            prepend-inner-icon="bx-search"
            clearable
          />
        </VCol>

        <VCol cols="12" md="3">
          <VSelect
            v-model="statusFilter"
            :items="statusOptions"
            :label="t('systemErrors.fields.status')"
            clearable
          />
        </VCol>
      </VRow>
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
