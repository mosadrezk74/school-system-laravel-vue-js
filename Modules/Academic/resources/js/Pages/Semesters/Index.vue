<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  semesters: { type: Object, required: true },
  academicYears: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const academicYearFilter = ref(props.filters.academic_year_id ?? null)
const isActiveFilter = ref(props.filters.is_active ?? null)

// Debounced so typing does not fire a request per keystroke.
let searchTimer
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 350)
})

watch([academicYearFilter, isActiveFilter], applyFilters)

function currentParams(extra = {}) {
  return {
    search: search.value || undefined,
    academic_year_id: academicYearFilter.value || undefined,
    is_active: isActiveFilter.value !== null ? isActiveFilter.value : undefined,
    ...extra,
  }
}

function applyFilters() {
  router.get('/academic/semesters', currentParams(), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

function goToPage(page) {
  router.get('/academic/semesters', currentParams({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}

const headers = computed(() => [
  { title: t('semesters.fields.name'), key: 'name', sortable: false },
  { title: t('semesters.fields.academicYear'), key: 'academic_year', sortable: false },
  { title: t('semesters.fields.sequence'), key: 'sequence' },
  { title: t('semesters.fields.startDate'), key: 'start_date' },
  { title: t('semesters.fields.endDate'), key: 'end_date' },
  { title: t('semesters.fields.isActive'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const activeOptions = computed(() => [
  { title: t('common.all'), value: null },
  { title: t('common.yes'), value: true },
  { title: t('common.no'), value: false },
])

const academicYearOptions = computed(() => props.academicYears.map(year => ({
  title: year.name,
  value: year.id,
})))

function archive(item) {
  askConfirm({
    message: t('semesters.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/academic/semesters/${item.id}`, { preserveScroll: true })
    },
  })
}

function restore(item) {
  askConfirm({
    message: t('common.confirmRestore'),
    color: 'primary',
    onConfirm: () => {
      router.patch(`/academic/semesters/${item.id}/restore`, {}, { preserveScroll: true })
    },
  })
}
</script>

<template>
  <Head :title="t('semesters.title')" />

  <AppPageHeader
    :title="t('semesters.title')"
    :subtitle="t('semesters.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/academic/semesters/create"
        :as="Link"
        color="primary"
      >
        {{ t('semesters.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="semesters.data"
    mobile-title-key="name"
    :meta="semesters.meta"
    @page="goToPage"
  >
    <template #filters>
      <VRow>
        <VCol cols="12" md="4">
          <VTextField
            v-model="search"
            :label="t('semesters.search')"
            prepend-inner-icon="bx-search"
            clearable
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="academicYearFilter"
            :items="academicYearOptions"
            :label="t('semesters.fields.academicYear')"
            clearable
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="isActiveFilter"
            :items="activeOptions"
            :label="t('semesters.fields.isActive')"
          />
        </VCol>
      </VRow>
    </template>

      <template #item.name="{ item }">
        <div class="d-flex align-center gap-2">
          <AppTableLink :href="`/academic/semesters/${item.id}`">
            {{ item.name }}
          </AppTableLink>
          <VChip v-if="item.deleted_at" size="x-small" color="warning">
            {{ t('common.archived') }}
          </VChip>
        </div>
      </template>

      <template #item.academic_year="{ item }">
        {{ item.academic_year?.name ?? '—' }}
      </template>

      <template #item.start_date="{ item }">
        {{ item.start_date ?? '—' }}
      </template>

      <template #item.end_date="{ item }">
        {{ item.end_date ?? '—' }}
      </template>

      <template #item.is_active="{ item }">
        <VChip :color="item.is_active ? 'success' : 'secondary'" size="small">
          {{ item.is_active ? t('common.active') : t('common.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/academic/semesters/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn v-if="!item.deleted_at" @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
        <IconBtn v-else @click="restore(item)">
          <VIcon icon="bx-refresh" />
        </IconBtn>
      </template>
  </AppDataTable>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
