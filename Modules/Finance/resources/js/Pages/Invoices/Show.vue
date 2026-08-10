<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  invoice: { type: Object, required: true },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()
const { formatMoney, formatDate, formatDateTime } = useMoney()

const statusColors = {
  pending: 'warning',
  partial: 'info',
  paid: 'success',
  cancelled: 'secondary',
  overdue: 'error',
}

const currency = computed(() => props.invoice.currency ?? 'SAR')

const canCancel = computed(() => props.invoice.status !== 'cancelled'
  && !(props.invoice.payments ?? []).length)

const itemHeaders = computed(() => [
  { title: t('finance.fields.fee_structure_id'), key: 'fee_structure' },
  { title: t('finance.quantity'), key: 'quantity' },
  { title: t('finance.unitPrice'), key: 'unit_price' },
  { title: t('finance.fields.discount_amount'), key: 'discount_amount' },
  { title: t('finance.lineTotal'), key: 'total', align: 'end' },
])

const paymentHeaders = computed(() => [
  { title: t('finance.fields.paid_at'), key: 'paid_at' },
  { title: t('finance.fields.payment_method'), key: 'payment_method' },
  { title: t('finance.fields.reference_number'), key: 'reference_number' },
  { title: t('finance.fields.amount'), key: 'amount', align: 'end' },
])

function cancelInvoice() {
  askConfirm({
    message: t('finance.confirmCancel'),
    color: 'error',
    onConfirm: () => {
      router.patch(`/finance/invoices/${props.invoice.id}/cancel`, {}, { preserveScroll: true })
    },
  })
}
</script>

<template>
  <Head :title="invoice.invoice_number" />

  <AppPageHeader
    :title="invoice.invoice_number"
    :subtitle="`${invoice.student?.name ?? '—'}`
      + ` · ${t('finance.fields.issue_date')}: ${formatDate(invoice.issue_date)}`
      + ` · ${t('finance.fields.due_date')}: ${formatDate(invoice.due_date)}`"
  >
    <template #actions>
      <VChip
        :color="statusColors[invoice.status] ?? 'secondary'"
        variant="tonal"
        size="small"
      >
        {{ invoice.status_label ?? '—' }}
      </VChip>
      <div class="text-end">
        <p class="text-caption text-medium-emphasis mb-0">
          {{ t('finance.fields.balance_due') }}
        </p>
        <h5 class="text-h5 mb-0">
          {{ formatMoney(invoice.balance_due, currency) }}
        </h5>
      </div>
      <VBtn
        v-if="canCancel"
        variant="tonal"
        color="error"
        prepend-icon="bx-x-circle"
        @click="cancelInvoice"
      >
        {{ t('finance.cancelInvoice') }}
      </VBtn>
      <VBtn
        variant="tonal"
        color="secondary"
        prepend-icon="bx-edit"
        :href="`/finance/invoices/${invoice.id}/edit`"
        :as="Link"
      >
        {{ t('common.edit') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VRow>

    <VCol cols="12" md="4">
      <VCard>
        <VCardText class="d-flex align-center gap-4">
          <VAvatar color="info" variant="tonal" size="42">
            <VIcon icon="bx-calculator" />
          </VAvatar>
          <div>
            <p class="text-caption text-medium-emphasis mb-0">
              {{ t('finance.fields.total_amount') }}
            </p>
            <h6 class="text-h6 mb-0">
              {{ formatMoney(invoice.total_amount, currency) }}
            </h6>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard>
        <VCardText class="d-flex align-center gap-4">
          <VAvatar color="success" variant="tonal" size="42">
            <VIcon icon="bx-check-circle" />
          </VAvatar>
          <div>
            <p class="text-caption text-medium-emphasis mb-0">
              {{ t('finance.fields.paid_amount') }}
            </p>
            <h6 class="text-h6 mb-0">
              {{ formatMoney(invoice.paid_amount, currency) }}
            </h6>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard>
        <VCardText class="d-flex align-center gap-4">
          <VAvatar color="warning" variant="tonal" size="42">
            <VIcon icon="bx-wallet" />
          </VAvatar>
          <div>
            <p class="text-caption text-medium-emphasis mb-0">
              {{ t('finance.fields.balance_due') }}
            </p>
            <h6 class="text-h6 mb-0">
              {{ formatMoney(invoice.balance_due, currency) }}
            </h6>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-list-ul" />
          </template>
          <VCardTitle>{{ t('finance.sections.items') }}</VCardTitle>
        </VCardItem>

        <VDataTable
          :headers="itemHeaders"
          :items="invoice.items ?? []"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.fee_structure="{ item }">
            {{ item.fee_structure?.name ?? '—' }}
          </template>

          <template #item.unit_price="{ item }">
            {{ formatMoney(item.unit_price, currency) }}
          </template>

          <template #item.discount_amount="{ item }">
            {{ formatMoney(item.discount_amount, currency) }}
          </template>

          <template #item.total="{ item }">
            <strong>{{ formatMoney(item.total, currency) }}</strong>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-list-ul"
          :title="t('finance.invoices.noItems')"
        />
      </template>
        </VDataTable>

        <VDivider />

        <VCardText class="d-flex justify-end">
          <div style="min-inline-size: 260px;">
            <div class="d-flex justify-space-between mb-2">
              <span class="text-medium-emphasis">{{ t('finance.fields.subtotal') }}</span>
              <span>{{ formatMoney(invoice.subtotal, currency) }}</span>
            </div>
            <div class="d-flex justify-space-between mb-2">
              <span class="text-medium-emphasis">{{ t('finance.fields.discount_amount') }}</span>
              <span>− {{ formatMoney(invoice.discount_amount, currency) }}</span>
            </div>
            <VDivider class="my-2" />
            <div class="d-flex justify-space-between text-h6">
              <strong>{{ t('finance.fields.total_amount') }}</strong>
              <strong>{{ formatMoney(invoice.total_amount, currency) }}</strong>
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-credit-card" />
          </template>
          <VCardTitle>{{ t('finance.payments.title') }}</VCardTitle>

          <template #append>
            <VBtn
              variant="tonal"
              prepend-icon="bx-plus"
              href="/finance/payments/create"
              :as="Link"
            >
              {{ t('finance.payments.addNew') }}
            </VBtn>
          </template>
        </VCardItem>

        <VDataTable
          :headers="paymentHeaders"
          :items="invoice.payments ?? []"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.paid_at="{ item }">
            {{ formatDateTime(item.paid_at) }}
          </template>

          <template #item.payment_method="{ item }">
            <VChip size="small" variant="tonal">
              {{ item.payment_method_label ?? '—' }}
            </VChip>
          </template>

          <template #item.reference_number="{ item }">
            {{ item.reference_number ?? '—' }}
          </template>

          <template #item.amount="{ item }">
            <strong>{{ formatMoney(item.amount, currency) }}</strong>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-credit-card"
          :title="t('finance.payments.empty')"
        />
      </template>
        </VDataTable>
      </VCard>
    </VCol>

    <VCol v-if="invoice.notes" cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-note" />
          </template>
          <VCardTitle>{{ t('finance.fields.notes') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          {{ invoice.notes }}
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
