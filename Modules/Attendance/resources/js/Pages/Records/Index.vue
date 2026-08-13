<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import AppDateField from '@/components/AppDateField.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  records: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/attendance/records',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
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

  <AppDataTable
    :headers="headers"
    :items="records.data"
    mobile-title-key="date"
    :meta="records.meta"
    empty-icon="bx-calendar-check"
    :empty-title="t('attendanceRecords.empty')"
    @page="goToPage"
  >
    <template #filters>
      <AppFilterBar
        :definitions="filterDefinitions"
        :filters="filters"
        :active-keys="activeKeys"
        @clear="clear"
        @clear-all="reset"
      />
    </template>

      <!--
        An attendance record is a per-session register, so the date is what
        identifies the row — there is no name or code to click.
      -->
      <template #item.date="{ item }">
        <AppTableLink :href="`/attendance/records/${item.id}`">
          {{ item.date ?? '—' }}
        </AppTableLink>
      </template>

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
        <IconBtn :href="`/attendance/records/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="destroy(item)">
          <VIcon icon="bx-trash" />
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
