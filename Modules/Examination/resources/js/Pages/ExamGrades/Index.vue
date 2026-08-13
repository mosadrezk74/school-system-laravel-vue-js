<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  rows: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  gradeLevels: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')

function applyFilters() {
  router.get('/examination/grades', { search: search.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

/*
 * Paging keeps the active search so moving to page 2 of a filtered list does
 * not silently drop the filter. Previously this was an inline arrow in the
 * template, which put routing logic in the markup.
 */
function goToPage(page) {
  router.get('/examination/grades', { search: search.value, page }, {
    preserveState: true,
    preserveScroll: true,
  })
}

let searchTimer
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 350)
})

const headers = computed(() => [
  { title: t('examGrades.gradeName'), key: 'grade_name' },
  { title: t('examGrades.gradeLevel'), key: 'grade_level' },
  { title: t('examGrades.subject'), key: 'subject' },
  { title: t('examGrades.gradePoint'), key: 'grade_point' },
  { title: t('examGrades.range'), key: 'range', sortable: false },
  { title: t('examGrades.isPassing'), key: 'is_passing', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const dialog = ref(false)
const editingGrade = ref(null)

const gradeLevelOptions = computed(() => props.gradeLevels.map(g => ({ title: g.name, value: g.id })))
const subjectOptions = computed(() => props.subjects.map(s => ({ title: s.name, value: s.id })))

const form = useForm({
  grade_level_id: null,
  subject_id: null,
  grade_name: '',
  grade_point: 4,
  min_percentage: 0,
  max_percentage: 100,
  is_passing: true,
})

function openCreate() {
  editingGrade.value = null
  form.reset()
  form.grade_point = 4
  form.min_percentage = 0
  form.max_percentage = 100
  form.is_passing = true
  dialog.value = true
}

function openEdit(grade) {
  editingGrade.value = grade
  form.grade_level_id = grade.grade_level_id
  form.subject_id = grade.subject_id
  form.grade_name = grade.grade_name
  form.grade_point = grade.grade_point
  form.min_percentage = grade.min_percentage
  form.max_percentage = grade.max_percentage
  form.is_passing = grade.is_passing
  dialog.value = true
}

function submit() {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      dialog.value = false
    },
  }

  if (editingGrade.value)
    form.put(`/examination/grades/${editingGrade.value.id}`, options)
  else
    form.post('/examination/grades', options)
}

function remove(grade) {
  askConfirm({
    message: t('examGrades.confirmDelete', { name: grade.grade_name }),
    color: 'error',
    onConfirm: () => {
      router.delete(`/examination/grades/${grade.id}`, { preserveScroll: true })
    },
  })
}
</script>

<template>
  <Head :title="t('examGrades.title')" />

  <AppPageHeader
    :title="t('examGrades.title')"
    :subtitle="t('examGrades.subtitle')"
  >
    <template #actions>
      <VBtn prepend-icon="bx-plus" @click="openCreate">
        {{ t('examGrades.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        maxlength="100"
        :label="t('examGrades.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 360px; flex: 1 1 260px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="rows.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.grade_level="{ item }">
        {{ item.grade_level?.name ?? '—' }}
      </template>

      <template #item.subject="{ item }">
        {{ item.subject?.name ?? t('examGrades.allSubjects') }}
      </template>

      <template #item.range="{ item }">
        {{ item.min_percentage }}% – {{ item.max_percentage }}%
      </template>

      <template #item.is_passing="{ item }">
        <VChip :color="item.is_passing ? 'success' : 'secondary'" size="small">
          {{ item.is_passing ? t('common.yes') : t('common.no') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn @click="openEdit(item)">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="remove(item)">
          <VIcon icon="bx-trash" />
        </IconBtn>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-medal"
          :title="t('examGrades.empty')"
        />
      </template>
    </VDataTable>

    <AppPagination
      :meta="rows.meta"
      @change="goToPage"
    />
  </VCard>

  <VDialog v-model="dialog" max-width="560">
    <VCard>
      <VCardItem>
        <VCardTitle>{{ editingGrade ? t('examGrades.edit') : t('examGrades.addNew') }}</VCardTitle>
      </VCardItem>
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.grade_level_id"
              :items="gradeLevelOptions"
              :label="t('examGrades.gradeLevel')"
              :error-messages="form.errors.grade_level_id"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.subject_id"
              clearable
              :items="subjectOptions"
              :label="t('examGrades.subject')"
              :hint="t('examGrades.hints.subject')"
              persistent-hint
              :error-messages="form.errors.subject_id"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.grade_name"
              maxlength="10"
              :counter="10"
              :label="t('examGrades.gradeName')"
              :error-messages="form.errors.grade_name"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VTextField
              v-model="form.grade_point"
              inputmode="decimal"
              max="9.99"
              step="0.01"
              type="number"
              min="0"
              :label="t('examGrades.gradePoint')"
              :error-messages="form.errors.grade_point"
            />
          </VCol>
          <VCol cols="12" md="4" class="d-flex align-center">
            <VSwitch
              v-model="form.is_passing"
              :label="t('examGrades.isPassing')"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.min_percentage"
              inputmode="decimal"
              step="0.01"
              type="number"
              min="0"
              max="100"
              :label="t('examGrades.minPercentage')"
              :error-messages="form.errors.min_percentage"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.max_percentage"
              inputmode="decimal"
              step="0.01"
              type="number"
              min="0"
              max="100"
              :label="t('examGrades.maxPercentage')"
              :error-messages="form.errors.max_percentage"
            />
          </VCol>
        </VRow>
      </VCardText>
      <VCardText class="d-flex justify-end gap-3">
        <VBtn variant="tonal" color="secondary" @click="dialog = false">
          {{ t('common.cancel') }}
        </VBtn>
        <VBtn :loading="form.processing" @click="submit">
          {{ t('common.save') }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
