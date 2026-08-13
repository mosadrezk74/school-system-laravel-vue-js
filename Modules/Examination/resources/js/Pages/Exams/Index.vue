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
  exams: { type: Object, required: true },
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
  url: '/examination/exams',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})






const headers = computed(() => [
  { title: t('exams.name'), key: 'name' },
  { title: t('exams.examType'), key: 'exam_type' },
  { title: t('exams.startDate'), key: 'start_date' },
  { title: t('exams.endDate'), key: 'end_date' },
  { title: t('exams.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('exams.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/examination/exams/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('exams.title')" />

  <AppPageHeader
    :title="t('exams.title')"
    :subtitle="t('exams.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :to="'/examination/exams/create'"
        :href="'/examination/exams/create'"
        :as="Link"
      >
        {{ t('exams.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="exams.data"
    mobile-title-key="name"
    :meta="exams.meta"
    empty-icon="bx-notepad"
    :empty-title="t('exams.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('exams.search')"
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
        <AppTableLink :href="`/examination/exams/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.exam_type="{ item }">
        {{ t(`exams.types.${item.exam_type}`, item.exam_type) }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_published ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_published ? t('exams.published') : t('exams.unpublished') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/examination/exams/${item.id}/edit`" :as="Link">
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
