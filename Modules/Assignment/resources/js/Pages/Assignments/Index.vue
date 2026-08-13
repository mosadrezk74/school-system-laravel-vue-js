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
  assignments: { type: Object, required: true },
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
  url: '/assignments/assignments',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})






const headers = computed(() => [
  { title: t('assignments.title'), key: 'title' },
  { title: t('assignments.class'), key: 'class' },
  { title: t('assignments.subject'), key: 'subject' },
  { title: t('assignments.teacher'), key: 'teacher' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('assignments.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/assignments/assignments/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('assignments.title')" />

  <AppPageHeader
    :title="t('assignments.title')"
    :subtitle="t('assignments.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :to="'/assignments/assignments/create'"
        :href="'/assignments/assignments/create'"
        :as="Link"
      >
        {{ t('assignments.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="assignments.data"
    mobile-title-key="title"
    :meta="assignments.meta"
    empty-icon="bx-task"
    :empty-title="t('assignments.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('assignments.search')"
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

      <template #item.title="{ item }">
        <AppTableLink :href="`/assignments/assignments/${item.id}`">
          {{ item.title ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.class="{ item }">
        {{ item.class ? item.class.name : '—' }}
      </template>

      <template #item.subject="{ item }">
        {{ item.subject?.name ?? '—' }}
      </template>

      <template #item.teacher="{ item }">
        {{ item.teacher?.name ?? '—' }}
      </template>

      <template #item.deadline="{ item }">
        {{ item.deadline ? new Date(item.deadline).toLocaleString() : '—' }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_published ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_published ? t('assignments.published') : t('assignments.unpublished') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/assignments/assignments/${item.id}/edit`" :as="Link">
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
