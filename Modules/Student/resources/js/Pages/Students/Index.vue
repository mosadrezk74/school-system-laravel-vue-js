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
  students: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.is_active ?? null)

// Omit the key entirely when cleared, rather than sending an empty string:
// $request->has('is_active') would still be true for '', so the backend
// would read that as an explicit "false" instead of "no filter".
function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (status.value !== null)
    query.is_active = status.value

  return query
}

function applyFilters() {
  router.get('/user-management/students', buildQuery(), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

let searchTimer
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 350)
})

watch(status, applyFilters)

const headers = computed(() => [
  { title: t('students.name'), key: 'name' },
  { title: t('students.email'), key: 'email' },
  { title: t('students.admissionNumber'), key: 'admission_number' },
  { title: t('students.academicYear'), key: 'academic_year' },
  { title: t('students.currentClass'), key: 'current_class' },
  { title: t('students.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('students.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/user-management/students/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/user-management/students', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('students.title')" />

  <AppPageHeader
    :title="t('students.title')"
    :subtitle="t('students.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :to="'/user-management/students/create'"
        :href="'/user-management/students/create'"
        :as="Link"
      >
        {{ t('students.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="students.data"
    mobile-title-key="name"
    :meta="students.meta"
    empty-icon="bx-user"
    :empty-title="t('students.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="search"
            :label="t('students.search')"
            prepend-inner-icon="bx-search"
            clearable
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="status"
            :label="t('common.filter')"
            :items="[
              { title: t('common.all'), value: null },
              { title: t('students.active'), value: true },
              { title: t('students.inactive'), value: false },
            ]"
            clearable
          />
        </VCol>
      </VRow>
    </template>

      <template #item.name="{ item }">
        <AppTableLink :href="`/user-management/students/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.academic_year="{ item }">
        {{ item.academic_year?.name }}
      </template>

      <template #item.current_class="{ item }">
        {{ item.current_class ? item.current_class.name : '—' }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_active ? t('students.active') : t('students.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/user-management/students/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
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
