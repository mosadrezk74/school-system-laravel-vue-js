<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  payment: { type: Object, required: true },
  refunds: { type: Object, default: () => ({ data: [] }) },
})

const { t } = useI18n()
const { formatMoney, formatDate, formatDateTime } = useMoney()
const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const refundRows = computed(() => props.refunds.data ?? props.refunds ?? [])

const statusColors = {
  pending: 'warning',
  paid: 'success',
  failed: 'error',
  cancelled: 'secondary',
  refunded: 'info',
  partially_refunded: 'info',
}

const showRefundForm = ref(false)

/*
 * Shown so the admin knows the ceiling, but the server recomputes it under a
 * lock before writing — this figure is guidance, not the rule.
 */
const refundable = computed(() => Number(props.payment.refundable_amount ?? 0))

const refundForm = useForm({
  payment_id: props.payment.id,
  amount: null,
  refund_date: new Date().toISOString().slice(0, 10),
  refund_method: null,
  reason: '',
  notes: '',
})

function submitRefund() {
  refundForm.post('/finance/refunds', {
    preserveScroll: true,
    onSuccess: () => {
      showRefundForm.value = false
      refundForm.reset('amount', 'reason', 'notes')
    },
  })
}

function confirmPayment() {
  askConfirm({
    message: t('finance.confirm_payment_message'),
    color: 'success',
    onConfirm: () => {
      router.patch(`/finance/payments/${props.payment.id}/confirm`, {}, { preserveScroll: true })
    },
  })
}

function cancelPayment() {
  askConfirm({
    message: t('finance.cancel_payment_message'),
    color: 'error',
    onConfirm: () => {
      router.patch(`/finance/payments/${props.payment.id}/cancel`, {}, { preserveScroll: true })
    },
  })
}
</script>

<template>
  <Head :title="t('finance.payments.title')" />

  <AppPageHeader
    :title="formatMoney(payment.amount)"
    :subtitle="`${payment.student?.name ?? '—'} · ${formatDateTime(payment.paid_at)}`"
  >
    <template #actions>
      <VChip
        :color="statusColors[payment.status] ?? 'secondary'"
        variant="tonal"
        size="small"
      >
        {{ payment.status_label ?? '—' }}
      </VChip>

      <VBtn
        v-if="payment.awaiting_confirmation"
        color="success"
        prepend-icon="bx-check"
        @click="confirmPayment"
      >
        {{ t('finance.confirm_payment') }}
      </VBtn>

      <VBtn
        v-if="payment.awaiting_confirmation"
        variant="tonal"
        color="error"
        prepend-icon="bx-x"
        @click="cancelPayment"
      >
        {{ t('finance.cancel_payment') }}
      </VBtn>

      <VBtn
        v-if="payment.is_refundable && refundable > 0"
        variant="tonal"
        color="warning"
        prepend-icon="bx-undo"
        @click="showRefundForm = !showRefundForm"
      >
        {{ t('finance.record_refund') }}
      </VBtn>
      <VBtn
        v-if="payment.invoice"
        variant="tonal"
        color="secondary"
        prepend-icon="bx-receipt"
        :href="`/finance/invoices/${payment.invoice_id}`"
        :as="Link"
      >
        {{ t('finance.viewInvoice') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VRow>
    <VCol v-if="showRefundForm" cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-undo" />
          </template>
          <VCardTitle>{{ t('finance.record_refund') }}</VCardTitle>
        </VCardItem>

        <VCardText>
          <VRow>
            <VCol cols="12" md="4">
              <div class="text-caption text-medium-emphasis">
                {{ t('finance.fields.amount') }}
              </div>
              <div class="text-body-1">
                {{ formatMoney(payment.amount) }}
              </div>
            </VCol>

            <VCol cols="12" md="4">
              <div class="text-caption text-medium-emphasis">
                {{ t('finance.refunded_amount') }}
              </div>
              <div class="text-body-1">
                {{ formatMoney(payment.refunded_amount) }}
              </div>
            </VCol>

            <VCol cols="12" md="4">
              <div class="text-caption text-medium-emphasis">
                {{ t('finance.refundable_amount') }}
              </div>
              <div class="text-body-1 font-weight-medium">
                {{ formatMoney(refundable) }}
              </div>
            </VCol>

            <VCol cols="12" md="4">
              <VTextField
                v-model.number="refundForm.amount"
                type="number"
                step="0.01"
                min="0"
                :max="refundable"
                :label="t('finance.refund_amount')"
                :error-messages="refundForm.errors.amount"
              />
            </VCol>

            <VCol cols="12" md="4">
              <VTextField
                v-model="refundForm.refund_date"
                type="date"
                :label="t('finance.fields.refund_date')"
                :error-messages="refundForm.errors.refund_date"
              />
            </VCol>

            <VCol cols="12" md="4">
              <VTextField
                v-model="refundForm.reason"
                :label="t('finance.fields.reason')"
                :error-messages="refundForm.errors.reason"
              />
            </VCol>

            <VCol cols="12">
              <VTextarea
                v-model="refundForm.notes"
                rows="2"
                :label="t('finance.fields.notes')"
                :error-messages="refundForm.errors.notes"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="px-4 pb-4">
          <VSpacer />
          <VBtn
            variant="tonal"
            color="secondary"
            @click="showRefundForm = false"
          >
            {{ t('common.cancel') }}
          </VBtn>
          <VBtn :loading="refundForm.processing" @click="submitRefund">
            {{ t('common.save') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VCol>

    <VCol v-if="refundRows.length" cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-history" />
          </template>
          <VCardTitle>{{ t('finance.refunds_title') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem
              v-for="refund in refundRows"
              :key="refund.id"
            >
              <template #prepend>
                <strong>{{ formatMoney(refund.amount) }}</strong>
              </template>
              <VListItemSubtitle class="ms-4">
                {{ formatDate(refund.refund_date) }}
                <span v-if="refund.reason"> · {{ refund.reason }}</span>
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-detail" />
          </template>
          <VCardTitle>{{ t('finance.sections.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.reference_number') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.reference_number ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.admissionNumber') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.student?.admission_number ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.notes') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.notes ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-receipt" />
          </template>
          <VCardTitle>{{ t('finance.fields.invoice_id') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.invoice_number') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.invoice?.invoice_number ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.total_amount') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.invoice ? formatMoney(payment.invoice.total_amount) : '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.balance_due') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.invoice ? formatMoney(payment.invoice.balance_due) : '—' }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
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
