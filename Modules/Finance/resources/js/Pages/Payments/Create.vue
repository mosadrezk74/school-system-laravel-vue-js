<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'

const props = defineProps({
  invoices: { type: Array, default: () => [] },
  paymentMethods: { type: Array, default: () => [] },
})

const { t } = useI18n()
const { formatMoney } = useMoney()

const required = label => `${label} *`

const invoiceOptions = computed(() => props.invoices.map(invoice => ({
  title: `${invoice.invoice_number} — ${invoice.student_name ?? ''}`,
  value: invoice.id,
})))

const paymentMethodOptions = computed(() => props.paymentMethods.map(m => ({ title: m.label, value: m.value })))

const form = useForm({
  invoice_id: null,
  amount: '',
  payment_method: null,
  reference_number: '',
  paid_at: new Date().toISOString().slice(0, 16),
  notes: '',
})

const selectedInvoice = computed(() => props.invoices.find(i => i.id === form.invoice_id) ?? null)

/*
 * The Action rejects anything above the outstanding balance, so the remaining
 * amount is pre-filled — the cashier overrides it only for a part payment.
 */
watch(() => form.invoice_id, () => {
  if (selectedInvoice.value)
    form.amount = selectedInvoice.value.balance_due
})

function submit() {
  form.post('/finance/payments')
}
</script>

<template>
  <Head :title="t('finance.payments.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.payments.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.payments.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <div class="d-flex align-center gap-3 mb-6">
          <VAvatar color="primary" variant="tonal" size="40">
            <VIcon icon="bx-credit-card" />
          </VAvatar>
          <div>
            <h6 class="text-h6">
              {{ t('finance.payments.sectionTitle') }}
            </h6>
            <p class="text-body-2 text-medium-emphasis mb-0">
              {{ t('finance.payments.sectionDesc') }}
            </p>
          </div>
        </div>

        <VRow>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.invoice_id"
              :items="invoiceOptions"
              :label="required(t('finance.fields.invoice_id'))"
              :hint="t('finance.hints.invoice')"
              persistent-hint
              :error-messages="form.errors.invoice_id"
              required
            />
          </VCol>

          <VCol cols="12" md="6">
            <VAlert
              v-if="selectedInvoice"
              type="info"
              variant="tonal"
              density="compact"
            >
              {{ t('finance.fields.balance_due') }}:
              <strong>{{ formatMoney(selectedInvoice.balance_due) }}</strong>
              ({{ t('finance.fields.total_amount') }}: {{ formatMoney(selectedInvoice.total_amount) }})
            </VAlert>
          </VCol>

          <VCol cols="12" md="4">
            <VTextField
              v-model="form.amount"
              type="number"
              step="0.01"
              min="0"
              :label="required(t('finance.fields.amount'))"
              :hint="t('finance.hints.paymentAmount')"
              persistent-hint
              :error-messages="form.errors.amount"
              required
            />
          </VCol>

          <VCol cols="12" md="4">
            <VSelect
              v-model="form.payment_method"
              :items="paymentMethodOptions"
              :label="required(t('finance.fields.payment_method'))"
              :hint="t('finance.hints.paymentMethod')"
              persistent-hint
              :error-messages="form.errors.payment_method"
              required
            />
          </VCol>

          <VCol cols="12" md="4">
            <VTextField
              v-model="form.paid_at"
              type="datetime-local"
              :label="required(t('finance.fields.paid_at'))"
              :hint="t('finance.hints.paidAt')"
              persistent-hint
              :error-messages="form.errors.paid_at"
              required
            />
          </VCol>

          <VCol cols="12" md="6">
            <VTextField
              v-model="form.reference_number"
              :label="t('finance.fields.reference_number')"
              :hint="t('finance.hints.referenceNumber')"
              persistent-hint
              :error-messages="form.errors.reference_number"
            />
          </VCol>

          <VCol cols="12">
            <VTextarea
              v-model="form.notes"
              rows="3"
              :label="t('finance.fields.notes')"
              :hint="t('finance.hints.notes')"
              persistent-hint
              :error-messages="form.errors.notes"
            />
          </VCol>
        </VRow>
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        href="/finance/payments"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn :loading="form.processing" @click="submit">
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
