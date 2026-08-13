<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import { useIndexFilters } from '@/composables/useIndexFilters'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  classrooms: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/academic/classrooms',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})




const headers = computed(() => [
  { title: t('classroom.fields.name'), key: 'name', sortable: false },
  { title: t('classroom.fields.room_type'), key: 'room_type' },
  { title: t('classroom.fields.capacity'), key: 'capacity' },
  { title: t('classroom.fields.is_active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const roomTypeLabels = {
  classroom: t('classroom.types.classroom'),
  lab: t('classroom.types.lab'),
  library: t('classroom.types.library'),
  auditorium: t('classroom.types.auditorium'),
  gym: t('classroom.types.gym'),
  music_room: t('classroom.types.music_room'),
  art_room: t('classroom.types.art_room'),
  computer_lab: t('classroom.types.computer_lab'),
  science_lab: t('classroom.types.science_lab'),
}

function archive(item) {
  askConfirm({
    message: t('classroom.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/academic/classrooms/${item.id}`, { preserveScroll: true })
    },
  })
}

function restore(item) {
  askConfirm({
    message: t('common.confirmRestore'),
    color: 'primary',
    onConfirm: () => {
      router.patch(`/academic/classrooms/${item.id}/restore`, {}, { preserveScroll: true })
    },
  })
}


</script>

<template>
  <Head :title="t('classroom.title')" />

  <AppPageHeader
    :title="t('classroom.title')"
    :subtitle="t('classroom.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :href="'/academic/classrooms/create'"
        :as="Link"
        color="primary"
      >
        {{ t('classroom.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="classrooms.data"
    mobile-title-key="name"
    :meta="classrooms.meta"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('classroom.search')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <AppFilterBar
        :definitions="filterDefinitions"
        :filters="filters"
        :active-keys="activeKeys"
        @clear="clear"
        @clear-all="reset"
      />
    </template>

      <template #item.name="{ item }">
        <AppTableLink :href="`/academic/classrooms/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.room_type="{ item }">
        <VChip size="small">
          {{ roomTypeLabels[item.room_type] || item.room_type }}
        </VChip>
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
        <IconBtn :href="`/academic/classrooms/${item.id}/edit`" :as="Link">
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
