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
  studyResources: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  subjects: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  types: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const subjectId = ref(props.filters.subject_id ?? null)
const gradeLevelId = ref(props.filters.grade_level_id ?? null)
const teacherId = ref(props.filters.teacher_id ?? null)
const type = ref(props.filters.type ?? null)

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (subjectId.value)
    query.subject_id = subjectId.value

  if (gradeLevelId.value)
    query.grade_level_id = gradeLevelId.value

  if (teacherId.value)
    query.teacher_id = teacherId.value

  if (type.value)
    query.type = type.value

  return query
}

function applyFilters() {
  router.get('/academic/study-resources', buildQuery(), {
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

watch([subjectId, gradeLevelId, teacherId, type], applyFilters)

const subjectOptions = computed(() => props.subjects.map(s => ({ title: s.name, value: s.id })))
const gradeLevelOptions = computed(() => props.gradeLevels.map(g => ({ title: g.name, value: g.id })))
const teacherOptions = computed(() => props.teachers.map(teacher => ({ title: teacher.name, value: teacher.id })))
const typeOptions = computed(() => props.types.map(item => ({ title: item.label, value: item.value })))

const headers = computed(() => [
  { title: t('studyResources.titleField'), key: 'title' },
  { title: t('studyResources.subject'), key: 'subject' },
  { title: t('studyResources.gradeLevel'), key: 'grade_level' },
  { title: t('studyResources.type'), key: 'type' },
  { title: t('studyResources.createdAt'), key: 'created_at' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function formatFileSize(bytes) {
  if (!bytes)
    return '—'

  const units = ['B', 'KB', 'MB', 'GB']
  let value = bytes
  let unit = 0

  while (value >= 1024 && unit < units.length - 1) {
    value /= 1024
    unit += 1
  }

  return `${value.toFixed(unit === 0 ? 0 : 1)} ${units[unit]}`
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString() : '—'
}

function archive(item) {
  askConfirm({
    message: t('studyResources.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/academic/study-resources/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/academic/study-resources', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('studyResources.title')" />

  <AppPageHeader
    :title="t('studyResources.title')"
    :subtitle="t('studyResources.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :href="'/academic/study-resources/create'"
        :as="Link"
      >
        {{ t('studyResources.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="studyResources.data"
    mobile-title-key="title"
    :meta="studyResources.meta"
    empty-icon="bx-folder-open"
    :empty-title="t('studyResources.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="search"
        :label="t('studyResources.search')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <VSelect
        v-model="subjectId"
        :label="t('studyResources.subject')"
        :items="subjectOptions"
        clearable
      />

      <VSelect
        v-model="gradeLevelId"
        :label="t('studyResources.gradeLevel')"
        :items="gradeLevelOptions"
        clearable
      />

      <VSelect
        v-model="teacherId"
        :label="t('studyResources.teacher')"
        :items="teacherOptions"
        clearable
      />

      <VSelect
        v-model="type"
        :label="t('studyResources.type')"
        :items="typeOptions"
        clearable
      />
    </template>

      <template #item.title="{ item }">
        <AppTableLink :href="`/academic/study-resources/${item.id}`">
          {{ item.title ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.subject="{ item }">
        {{ item.subject?.name ?? '—' }}
      </template>

      <template #item.grade_level="{ item }">
        {{ item.grade_level?.name ?? '—' }}
      </template>

      <template #item.teacher="{ item }">
        {{ item.teacher?.name ?? '—' }}
      </template>

      <template #item.type="{ item }">
        <VChip size="small" color="primary" variant="tonal">
          {{ item.type_label ?? '—' }}
        </VChip>
      </template>

      <template #item.file_size="{ item }">
        {{ formatFileSize(item.file_size) }}
      </template>

      <template #item.created_at="{ item }">
        {{ formatDate(item.created_at) }}
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/academic/study-resources/${item.id}/edit`" :as="Link">
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
