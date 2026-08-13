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
  paymentMethods: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
  types: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/finance/payment-methods',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})


const typeOptions = computed(() => props.types.map(item => ({ title: item.label, value: item.value })))





const headers = computed(() => [
  { title: t('finance.fields.name'), key: 'name' },
  { title: t('finance.fields.code'), key: 'code' },
  { title: t('finance.fields.type'), key: 'type' },
  { title: t('finance.fields.display_order'), key: 'display_order' },
  { title: t('finance.fields.is_active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('finance.payment_methods_archive_confirm'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/finance/payment-methods/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('finance.payment_methods_title')" />

  <AppPageHeader
    :title="t('finance.payment_methods_title')"
    :subtitle="t('finance.payment_methods_subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/finance/payment-methods/create"
        :as="Link"
      >
        {{ t('finance.add_payment_method') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="paymentMethods.data"
    mobile-title-key="name"
    :meta="paymentMethods.meta"
    empty-icon="bx-credit-card"
    :empty-title="t('finance.payment_methods_empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('finance.search_payment_methods')"
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
      <AppTableLink :href="`/finance/payment-methods/${item.id}`">
        {{ item.name ?? '—' }}
      </AppTableLink>
    </template>

    <template #item.type="{ item }">
      <div class="d-flex align-center gap-2">
        <span>{{ item.type_label ?? '—' }}</span>

        <!--
          An online method with no link sends the parent nowhere. The list is
          where an admin scans for what is misconfigured, so the gap is flagged
          here rather than only being discovered by a parent.
        -->
        <VTooltip
          v-if="item.type === 'online' && !item.payment_url"
          :text="t('finance.payment_url_missing')"
        >
          <template #activator="{ props: tooltipProps }">
            <VIcon
              v-bind="tooltipProps"
              icon="bx-error-circle"
              color="warning"
              size="18"
            />
          </template>
        </VTooltip>
      </div>
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
      <IconBtn :href="`/finance/payment-methods/${item.id}/edit`" :as="Link">
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
