<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  records: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const statusFilter = ref(props.filters.status ?? null)
const typeFilter = ref(props.filters.type ?? null)
const studentIdFilter = ref(props.filters.student_id ?? null)

// Omit each key entirely when cleared, rather than sending an empty string:
// $request->has('status') would still be true for '', so the backend would
// read that as an explicit filter instead of "no filter".
function buildQuery(extra = {}) {
  const query = { ...extra }

  if (statusFilter.value !== null)
    query.status = statusFilter.value

  if (typeFilter.value !== null)
    query.type = typeFilter.value

  if (studentIdFilter.value !== null)
    query.student_id = studentIdFilter.value

  return query
}

function applyFilters() {
  router.get('/behavior/records', buildQuery(), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

watch([statusFilter, typeFilter, studentIdFilter], applyFilters)

const headers = computed(() => [
  { title: t('behaviorRecords.student'), key: 'student' },
  { title: t('behaviorRecords.category'), key: 'category' },
  { title: t('behaviorRecords.points'), key: 'points' },
  { title: t('behaviorRecords.date'), key: 'date' },
  { title: t('behaviorRecords.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const statusOptions = computed(() => [
  { title: t('behaviorRecords.reported'), value: 'reported' },
  { title: t('behaviorRecords.reviewed'), value: 'reviewed' },
])

const typeOptions = computed(() => [
  { title: t('behaviorCategories.merit'), value: 'merit' },
  { title: t('behaviorCategories.violation'), value: 'violation' },
])

function remove(item) {
  if (!confirm(t('behaviorRecords.confirmDelete')))
    return

  router.delete(`/behavior/records/${item.id}`, { preserveScroll: true })
}

function goToPage(page) {
  router.get('/behavior/records', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('behaviorRecords.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('behaviorRecords.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('behaviorRecords.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :href="'/behavior/records/create'"
          :as="Link"
        >
          {{ t('behaviorRecords.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText>
      <VRow>
        <VCol cols="12" md="4">
          <VSelect
            v-model="statusFilter"
            :items="statusOptions"
            :label="t('behaviorRecords.status')"
            clearable
            density="compact"
          />
        </VCol>
        <VCol cols="12" md="4">
          <VSelect
            v-model="typeFilter"
            :items="typeOptions"
            :label="t('behaviorCategories.type')"
            clearable
            density="compact"
          />
        </VCol>
      </VRow>
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="records.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.student="{ item }">
        {{ item.student?.name }}
      </template>

      <template #item.category="{ item }">
        {{ item.behavior_category?.name }}
        <VChip
          :color="item.behavior_category?.type === 'merit' ? 'success' : 'error'"
          size="small"
          class="ms-2"
        >
          {{ item.behavior_category?.type === 'merit' ? t('behaviorCategories.merit') : t('behaviorCategories.violation') }}
        </VChip>
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.status === 'reviewed' ? 'success' : 'warning'"
          size="small"
        >
          {{ item.status === 'reviewed' ? t('behaviorRecords.reviewed') : t('behaviorRecords.reported') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/behavior/records/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/behavior/records/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="remove(item)">
          <VIcon icon="bx-trash" />
        </IconBtn>
      </template>

      <template #no-data>
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-shield-quarter" size="40" class="mb-2" />
          {{ t('behaviorRecords.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="records.meta && records.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="records.meta.current_page"
          :length="records.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
