<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  templates: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  providers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/reports/templates',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})


const categoryOptions = computed(() => props.categories.map(item => ({
  title: item.label,
  value: item.value,
})))

const statusOptions = computed(() => [
  { title: t('report.options.active'), value: '1' },
  { title: t('report.options.inactive'), value: '0' },
])





const headers = computed(() => [
  { title: t('report.fields.name'), key: 'name' },
  { title: t('report.fields.code'), key: 'code' },
  { title: t('report.fields.category'), key: 'category' },
  { title: t('report.fields.is_active'), key: 'is_active' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function categoryLabel(value) {
  return props.categories.find(item => item.value === value)?.label ?? '—'
}

function remove(item) {
  askConfirm({
    message: t('report.confirmDelete'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/reports/templates/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('report.templates.title')" />

  <AppPageHeader
    :title="t('report.templates.title')"
    :subtitle="t('report.templates.subtitle')"
  >
    <template #actions>
      <div class="d-flex gap-3">
        <VBtn
          variant="tonal"
          color="secondary"
          prepend-icon="bx-arrow-back"
          href="/reports"
          :as="Link"
        >
          {{ t('report.backToReports') }}
        </VBtn>
        <VBtn
          prepend-icon="bx-plus"
          href="/reports/templates/create"
          :as="Link"
        >
          {{ t('report.templates.addNew') }}
        </VBtn>
      </div>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="templates.data"
    :meta="templates.meta"
    empty-icon="bx-file"
    :empty-title="t('report.templates.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('report.searchTemplates')"
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

      <template #item.code="{ item }">
        {{ item.code ?? '—' }}
      </template>

      <template #item.category="{ item }">
        <VChip size="small" variant="tonal">
          {{ categoryLabel(item.category) }}
        </VChip>
      </template>

      <template #item.is_active="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
          variant="tonal"
        >
          {{ item.is_active ? t('report.options.active') : t('report.options.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/reports/templates/${item.id}/edit`" :as="Link">
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
