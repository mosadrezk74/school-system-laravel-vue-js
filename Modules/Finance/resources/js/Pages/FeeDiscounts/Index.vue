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
  feeDiscounts: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  discountTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()
const { formatMoney, formatDate } = useMoney()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/finance/fee-discounts',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})


const discountTypeOptions = computed(() => props.discountTypes.map(d => ({ title: d.label, value: d.value })))
const gradeLevelOptions = computed(() => props.gradeLevels.map(g => ({ title: g.name, value: g.id })))
const classroomOptions = computed(() => props.classrooms.map(c => ({ title: c.name, value: c.id })))

const statusOptions = computed(() => [
  { title: t('finance.active'), value: '1' },
  { title: t('finance.inactive'), value: '0' },
])





const headers = computed(() => [
  { title: t('finance.fields.name'), key: 'name' },
  { title: t('finance.fields.code'), key: 'code' },
  { title: t('finance.fields.discount_type'), key: 'discount_type' },
  { title: t('finance.fields.discount_value'), key: 'discount_value' },
  { title: t('finance.scope'), key: 'scope' },
  { title: t('finance.usage'), key: 'usage' },
  { title: t('finance.fields.is_active'), key: 'is_active' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function scopeLabel(item) {
  if (item.classroom)
    return item.classroom.name

  if (item.grade_level)
    return item.grade_level.name

  return t('finance.schoolWide')
}

function discountValueLabel(item) {
  return item.discount_type === 'percentage'
    ? `${Number(item.discount_value)}%`
    : formatMoney(item.discount_value)
}

function archive(item) {
  askConfirm({
    message: t('finance.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/finance/fee-discounts/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('finance.feeDiscounts.title')" />

  <AppPageHeader
    :title="t('finance.feeDiscounts.title')"
    :subtitle="t('finance.feeDiscounts.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/finance/fee-discounts/create"
        :as="Link"
      >
        {{ t('finance.feeDiscounts.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="feeDiscounts.data"
    mobile-title-key="name"
    :meta="feeDiscounts.meta"
    empty-icon="bx-gift"
    :empty-title="t('finance.feeDiscounts.empty')"
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
        <AppTableLink :href="`/finance/fee-discounts/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.code="{ item }">
        {{ item.code ?? '—' }}
      </template>

      <template #item.discount_type="{ item }">
        <VChip size="small" color="primary" variant="tonal">
          {{ item.discount_type_label ?? '—' }}
        </VChip>
      </template>

      <template #item.discount_value="{ item }">
        {{ discountValueLabel(item) }}
      </template>

      <template #item.scope="{ item }">
        <VChip size="small" variant="tonal">
          {{ scopeLabel(item) }}
        </VChip>
      </template>

      <template #item.usage="{ item }">
        {{ item.used_count }}<span v-if="item.usage_limit"> / {{ item.usage_limit }}</span>
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

      <template #item.actions="{ item }">
        <IconBtn :href="`/finance/fee-discounts/${item.id}/edit`" :as="Link">
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
