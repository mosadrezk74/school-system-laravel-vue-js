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
  subjects: { type: Object, required: true },
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
  url: '/academic/subjects',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})






const headers = computed(() => [
  { title: t('subject.fields.name'), key: 'name', sortable: false },
  { title: t('subject.fields.description'), key: 'description', sortable: false },
  { title: t('subject.fields.is_active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const activeOptions = computed(() => [
  { title: t('common.all'), value: null },
  { title: t('common.yes'), value: true },
  { title: t('common.no'), value: false },
])

function archive(item) {
  askConfirm({
    message: t('subject.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/academic/subjects/${item.id}`, { preserveScroll: true })
    },
  })
}

function restore(item) {
  askConfirm({
    message: t('common.confirmRestore'),
    color: 'primary',
    onConfirm: () => {
      router.patch(`/academic/subjects/${item.id}/restore`, {}, { preserveScroll: true })
    },
  })
}
</script>

<template>
  <Head :title="t('subject.title')" />

  <AppPageHeader
    :title="t('subject.title')"
    :subtitle="t('subject.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/academic/subjects/create"
        :as="Link"
        color="primary"
      >
        {{ t('subject.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="subjects.data"
    mobile-title-key="name"
    :meta="subjects.meta"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('subject.search')"
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
        <div class="d-flex align-center gap-2">
          <AppTableLink :href="`/academic/subjects/${item.id}`">
            {{ item.name || '—' }}
          </AppTableLink>
          <VChip v-if="item.deleted_at" size="x-small" color="warning">
            {{ t('common.archived') }}
          </VChip>
        </div>
      </template>

      <template #item.description="{ item }">
        {{ item.description || '—' }}
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
        <IconBtn :href="`/academic/subjects/${item.id}/edit`" :as="Link">
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
