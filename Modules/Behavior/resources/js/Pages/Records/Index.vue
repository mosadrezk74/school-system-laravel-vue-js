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
  records: { type: Object, required: true },
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
  url: '/behavior/records',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})





const headers = computed(() => [
  { title: t('behaviorRecords.student'), key: 'student' },
  { title: t('behaviorRecords.category'), key: 'category' },
  { title: t('behaviorRecords.points'), key: 'points' },
  { title: t('behaviorRecords.date'), key: 'date' },
  { title: t('behaviorRecords.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const statusOptions = computed(() => [
  { title: t('behaviorRecords.reported'), value: 'reported' },
  { title: t('behaviorRecords.reviewed'), value: 'reviewed' },
])

const typeOptions = computed(() => [
  { title: t('behaviorCategories.merit'), value: 'merit' },
  { title: t('behaviorCategories.violation'), value: 'violation' },
])

function remove(item) {
  askConfirm({
    message: t('behaviorRecords.confirmDelete'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/behavior/records/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('behaviorRecords.title')" />

  <AppPageHeader
    :title="t('behaviorRecords.title')"
    :subtitle="t('behaviorRecords.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :href="'/behavior/records/create'"
        :as="Link"
      >
        {{ t('behaviorRecords.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="records.data"
    mobile-title-key="student"
    :meta="records.meta"
    empty-icon="bx-shield-quarter"
    :empty-title="t('behaviorRecords.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('behaviorRecords.search')"
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

      <template #item.student="{ item }">
        <AppTableLink :href="`/behavior/records/${item.id}`">
          {{ item.student?.name }}
        </AppTableLink>
      </template>

      <template #item.category="{ item }">
        {{ item.behavior_category?.name }}
        <VChip
          :color="item.behavior_category?.type === 'merit' ? 'success' : 'error'"
          size="small"
          class="ms-2"
        >
          {{ item.behavior_category?.type === 'merit' ? t('behaviorCategories.merit') : t('behaviorCategories.violation') }}
        </VChip>
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.status === 'reviewed' ? 'success' : 'warning'"
          size="small"
        >
          {{ item.status === 'reviewed' ? t('behaviorRecords.reviewed') : t('behaviorRecords.reported') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/behavior/records/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="remove(item)">
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
