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
import AppDateField from '@/components/AppDateField.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  payments: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
  invoices: { type: Array, default: () => [] },
  paymentMethods: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()
const { formatMoney, formatDateTime } = useMoney()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/finance/payments',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})


const paymentMethodOptions = computed(() => props.paymentMethods.map(m => ({ title: m.label, value: m.value })))
const statusOptions = computed(() => props.statuses.map(s => ({ title: s.label, value: s.value })))





const headers = computed(() => [
  { title: t('finance.fields.paid_at'), key: 'paid_at' },
  { title: t('finance.fields.student_id'), key: 'student' },
  { title: t('finance.fields.invoice_id'), key: 'invoice' },
  { title: t('finance.fields.payment_method'), key: 'payment_method' },
  { title: t('finance.fields.reference_number'), key: 'reference_number' },
  { title: t('finance.fields.amount'), key: 'amount' },
  { title: t('finance.fields.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const statusColors = {
  pending: 'warning',
  paid: 'success',
  failed: 'error',
  cancelled: 'secondary',
  refunded: 'info',
  partially_refunded: 'info',
}

function confirmPayment(item) {
  askConfirm({
    message: t('finance.confirm_payment_message'),
    color: 'success',
    onConfirm: () => {
      router.patch(`/finance/payments/${item.id}/confirm`, {}, { preserveScroll: true })
    },
  })
}

function cancelPayment(item) {
  askConfirm({
    message: t('finance.cancel_payment_message'),
    color: 'error',
    onConfirm: () => {
      router.patch(`/finance/payments/${item.id}/cancel`, {}, { preserveScroll: true })
    },
  })
}

function archive(item) {
  askConfirm({
    message: t('finance.confirmArchivePayment'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/finance/payments/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('finance.payments.title')" />

  <AppPageHeader
    :title="t('finance.payments.title')"
    :subtitle="t('finance.payments.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/finance/payments/create"
        :as="Link"
      >
        {{ t('finance.payments.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="payments.data"
    mobile-title-key="student"
    :meta="payments.meta"
    empty-icon="bx-credit-card"
    :empty-title="t('finance.payments.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('finance.searchPayment')"
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

      <template #item.paid_at="{ item }">
        {{ formatDateTime(item.paid_at) }}
      </template>

      <template #item.student="{ item }">
        <AppTableLink :href="`/finance/payments/${item.id}`">
          {{ item.student?.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.invoice="{ item }">
        <Link
          v-if="item.invoice"
          :href="`/finance/invoices/${item.invoice_id}`"
        >
          {{ item.invoice.invoice_number }}
        </Link>
        <span v-else>—</span>
      </template>

      <template #item.payment_method="{ item }">
        <VChip size="small" color="primary" variant="tonal">
          {{ item.payment_method_label ?? '—' }}
        </VChip>
      </template>

      <template #item.reference_number="{ item }">
        {{ item.reference_number ?? '—' }}
      </template>

      <template #item.amount="{ item }">
        <strong>{{ formatMoney(item.amount) }}</strong>
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="statusColors[item.status] ?? 'secondary'"
          size="small"
          variant="tonal"
        >
          {{ item.status_label ?? '—' }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn
          v-if="item.awaiting_confirmation"
          :title="t('finance.confirm_payment')"
          @click="confirmPayment(item)"
        >
          <VIcon icon="bx-check" color="success" />
        </IconBtn>
        <IconBtn
          v-if="item.awaiting_confirmation"
          :title="t('finance.cancel_payment')"
          @click="cancelPayment(item)"
        >
          <VIcon icon="bx-x" color="error" />
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
