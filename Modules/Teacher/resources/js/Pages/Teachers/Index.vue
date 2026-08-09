<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  teachers: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const isActiveFilter = ref(props.filters.is_active ?? null)
const departmentFilter = ref(props.filters.department_id ?? null)
const genderFilter = ref(props.filters.gender ?? null)

let searchTimer
watch(search, value => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    applyFilters()
  }, 350)
})

watch([isActiveFilter, departmentFilter, genderFilter], () => {
  applyFilters()
})

// Omit each key entirely when cleared, rather than sending an empty string:
// $request->has('is_active') would still be true for '', so the backend
// would read that as an explicit filter instead of "no filter".
function buildQuery(extra = {}) {
  const query = { ...extra }

  if (search.value)
    query.search = search.value

  if (isActiveFilter.value !== null)
    query.is_active = isActiveFilter.value

  if (departmentFilter.value)
    query.department_id = departmentFilter.value

  if (genderFilter.value)
    query.gender = genderFilter.value

  return query
}

function applyFilters() {
  router.get('/user-management/teachers', buildQuery(), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

const headers = computed(() => [
  { title: t('teacher.fields.name'), key: 'name' },
  { title: t('teacher.fields.email'), key: 'email' },
  { title: t('teacher.fields.employee_id'), key: 'employee_id' },
  { title: t('teacher.fields.position'), key: 'position' },
  { title: t('teacher.fields.department_id'), key: 'department' },
  { title: t('teacher.fields.is_active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('teacher.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/user-management/teachers/${item.id}`, { preserveScroll: true })
    },
  })
}

function restore(item) {
  askConfirm({
    message: t('common.confirmRestore'),
    color: 'primary',
    onConfirm: () => {
      router.patch(`/user-management/teachers/${item.id}/restore`, {}, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/user-management/teachers', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}

const filterOptions = {
  active: [
    { title: t('common.all'), value: null },
    { title: t('common.yes'), value: true },
    { title: t('common.no'), value: false },
  ],
  gender: [
    { title: t('common.all'), value: null },
    { title: t('students.male'), value: 'male' },
    { title: t('students.female'), value: 'female' },
  ],
}
</script>

<template>
  <Head :title="t('teacher.title')" />

  <AppPageHeader
    :title="t('teacher.title')"
    :subtitle="t('teacher.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :href="'/user-management/teachers/create'"
        :as="Link"
        color="primary"
      >
        {{ t('teacher.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VDivider />

    <VCardText>
      <VRow>
        <VCol cols="12" md="4">
          <VTextField
            v-model="search"
            :label="t('teacher.search')"
            prepend-inner-icon="bx-search"
            clearable
            density="compact"
          />
        </VCol>

        <VCol cols="12" md="2">
          <VSelect
            v-model="isActiveFilter"
            :items="filterOptions.active"
            :label="t('teacher.fields.is_active')"
            density="compact"
            clearable
          />
        </VCol>

        <VCol cols="12" md="3">
          <VSelect
            v-model="genderFilter"
            :items="filterOptions.gender"
            :label="t('teacher.fields.gender')"
            density="compact"
            clearable
          />
        </VCol>

        <VCol cols="12" md="2">
          <VBtn
            variant="tonal"
            color="secondary"
            size="small"
            @click="applyFilters"
          >
            {{ t('common.filter') }}
          </VBtn>
        </VCol>
      </VRow>
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="teachers.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.name="{ item }">
        {{ item.name }}
      </template>

      <template #item.department="{ item }">
        {{ item.department?.name || '—' }}
      </template>

      <template #item.is_active="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_active ? t('common.active') : t('common.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/user-management/teachers/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/user-management/teachers/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn v-if="!item.deleted_at" @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
        <IconBtn v-else @click="restore(item)">
          <VIcon icon="bx-refresh" />
        </IconBtn>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-chalkboard"
          :title="t('teacher.empty')"
        />
      </template>
    </VDataTable>

    <AppPagination
      :meta="teachers.meta"
      @change="goToPage"
    />
  </VCard>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>