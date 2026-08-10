<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import InvoiceForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  students: { type: Array, default: () => [] },
  feeStructures: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()

const today = new Date().toISOString().slice(0, 10)

const form = useForm({
  student_id: null,
  invoice_number: '',
  issue_date: today,
  due_date: today,
  currency: 'SAR',
  notes: '',
  items: [
    {
      fee_structure_id: null,
      quantity: 1,
      unit_price: '',
      discount_amount: '',
    },
  ],
})

function submit() {
  form.post('/finance/invoices')
}
</script>

<template>
  <Head :title="t('finance.invoices.addNew')" />

  <AppPageHeader
    :title="t('finance.invoices.addNew')"
    :subtitle="t('finance.invoices.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <InvoiceForm
          :form="form"
          :students="students"
          :fee-structures="feeStructures"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/finance/invoices"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn :loading="form.processing" @click="submit">
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
