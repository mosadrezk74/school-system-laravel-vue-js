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
  teachers: { type: Object, required: true },
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
  url: '/user-management/teachers',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})





const headers = computed(() => [
  { title: t('teacher.fields.name'), key: 'name' },
  { title: t('teacher.fields.email'), key: 'email' },
  { title: t('teacher.fields.employee_id'), key: 'employee_id' },
  { title: t('teacher.fields.position'), key: 'position' },
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

  <AppDataTable
    :headers="headers"
    :items="teachers.data"
    mobile-title-key="name"
    :meta="teachers.meta"
    empty-icon="bx-chalkboard"
    :empty-title="t('teacher.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('teacher.search')"
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
        <AppTableLink :href="`/user-management/teachers/${item.id}`">
          {{ item.name }}
        </AppTableLink>
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