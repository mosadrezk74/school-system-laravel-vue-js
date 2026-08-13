<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  feeCategories: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()
const { formatDate } = useMoney()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/finance/fee-categories',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})


const statusOptions = computed(() => [
  { title: t('finance.active'), value: '1' },
  { title: t('finance.inactive'), value: '0' },
])





const headers = computed(() => [
  { title: t('finance.fields.name'), key: 'name' },
  { title: t('finance.fields.code'), key: 'code' },
  { title: t('finance.fields.description'), key: 'description' },
  { title: t('finance.fields.is_active'), key: 'is_active' },
  { title: t('finance.createdAt'), key: 'created_at' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('finance.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/finance/fee-categories/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('finance.feeCategories.title')" />

  <AppPageHeader
    :title="t('finance.feeCategories.title')"
    :subtitle="t('finance.feeCategories.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/finance/fee-categories/create"
        :as="Link"
      >
        {{ t('finance.feeCategories.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="feeCategories.data"
    mobile-title-key="name"
    :meta="feeCategories.meta"
    empty-icon="bx-purchase-tag"
    :empty-title="t('finance.feeCategories.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('finance.searchByName')"
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
        <AppTableLink :href="`/finance/fee-categories/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.code="{ item }">
        {{ item.code ?? '—' }}
      </template>

      <template #item.description="{ item }">
        {{ item.description ?? '—' }}
      </template>

      <template #item.is_active="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
          variant="tonal"
        >
          {{ item.is_active ? t('finance.active') : t('finance.inactive') }}
        </VChip>
      </template>

      <template #item.created_at="{ item }">
        {{ formatDate(item.created_at) }}
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/finance/fee-categories/${item.id}/edit`" :as="Link">
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
