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
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  timetables: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const headers = computed(() => [
  { title: t('timetables.class'), key: 'class' },
  { title: t('timetables.academicYear'), key: 'academic_year' },
  { title: t('timetables.semester'), key: 'semester' },
  { title: t('common.active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('timetables.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/schedule/timetables/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('timetables.title')" />

  <AppPageHeader
    :title="t('timetables.title')"
    :subtitle="t('timetables.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :href="'/schedule/timetables/create'"
        :as="Link"
      >
        {{ t('timetables.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="timetables.data"
    :meta="timetables.meta"
    mobile-title-key="class"
    empty-icon="bx-calendar-week"
    :empty-title="t('timetables.empty')"
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

      <template #item.class="{ item }">
        <AppTableLink :href="`/schedule/timetables/${item.id}`">
          {{ item.class?.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.academic_year="{ item }">
        {{ item.academic_year?.name }}
      </template>

      <template #item.semester="{ item }">
        {{ item.semester?.name ?? '-' }}
      </template>

      <template #item.is_active="{ item }">
        <VChip :color="item.is_active ? 'success' : 'secondary'" size="small">
          {{ item.is_active ? t('common.active') : t('common.no') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/schedule/timetables/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-grid-alt" />
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
