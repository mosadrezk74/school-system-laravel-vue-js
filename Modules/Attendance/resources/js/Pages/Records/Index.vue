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
  records: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const classId = ref(props.filters.class_id ?? null)
const subjectId = ref(props.filters.subject_id ?? null)
const dateFrom = ref(props.filters.date_from ?? null)
const dateTo = ref(props.filters.date_to ?? null)

function buildQuery(extra = {}) {
  const query = { ...extra }

  if (classId.value)
    query.class_id = classId.value
  if (subjectId.value)
    query.subject_id = subjectId.value
  if (dateFrom.value)
    query.date_from = dateFrom.value
  if (dateTo.value)
    query.date_to = dateTo.value

  return query
}

function applyFilters() {
  router.get('/attendance/records', buildQuery(), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

let filterTimer
watch([classId, subjectId], applyFilters)
watch([dateFrom, dateTo], () => {
  clearTimeout(filterTimer)
  filterTimer = setTimeout(applyFilters, 350)
})

const headers = computed(() => [
  { title: t('attendanceRecords.date'), key: 'date' },
  { title: t('attendanceRecords.session'), key: 'session' },
  { title: t('attendanceRecords.class'), key: 'class' },
  { title: t('attendanceRecords.subject'), key: 'subject' },
  { title: t('attendanceRecords.present'), key: 'present_count' },
  { title: t('attendanceRecords.absent'), key: 'absent_count' },
  { title: t('attendanceRecords.late'), key: 'late_count' },
  { title: t('attendanceRecords.excused'), key: 'excused_count' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function destroy(item) {
  askConfirm({
    message: t('attendanceRecords.confirmDelete'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/attendance/records/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/attendance/records', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('attendanceRecords.title')" />

  <AppPageHeader
    :title="t('attendanceRecords.title')"
    :subtitle="t('attendanceRecords.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :to="'/attendance/records/create'"
        :href="'/attendance/records/create'"
        :as="Link"
      >
        {{ t('attendanceRecords.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VSelect
        v-model="classId"
        :label="t('attendanceRecords.class')"
        :items="classes.map(c => ({ title: c.name, value: c.id }))"
        density="compact"
        clearable
        style="max-inline-size: 220px;"
      />

      <VSelect
        v-model="subjectId"
        :label="t('attendanceRecords.subject')"
        :items="subjects.map(s => ({ title: s.name, value: s.id }))"
        density="compact"
        clearable
        style="max-inline-size: 220px;"
      />


      <VTextField
        v-model="dateFrom"
        type="date"
        :label="t('attendanceRecords.dateFrom')"
        density="compact"
        clearable
        style="max-inline-size: 180px;"
      />

      <VTextField
        v-model="dateTo"
        type="date"
        :label="t('attendanceRecords.dateTo')"
        density="compact"
        clearable
        style="max-inline-size: 180px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="records.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.class="{ item }">
        {{ item.class ? item.class.name : '—' }}
      </template>

      <template #item.subject="{ item }">
        {{ item.subject?.name ?? '—' }}
      </template>

      <template #item.present_count="{ item }">
        <VChip color="success" size="small" variant="tonal">
          {{ item.present_count }}
        </VChip>
      </template>

      <template #item.absent_count="{ item }">
        <VChip color="error" size="small" variant="tonal">
          {{ item.absent_count }}
        </VChip>
      </template>

      <template #item.late_count="{ item }">
        <VChip color="warning" size="small" variant="tonal">
          {{ item.late_count }}
        </VChip>
      </template>

      <template #item.excused_count="{ item }">
        <VChip color="info" size="small" variant="tonal">
          {{ item.excused_count }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/attendance/records/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/attendance/records/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="destroy(item)">
          <VIcon icon="bx-trash" />
        </IconBtn>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-calendar-check"
          :title="t('attendanceRecords.empty')"
        />
      </template>
    </VDataTable>

    <AppPagination
      :meta="records.meta"
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
