<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import InvoiceForm from './_Form.vue'

const props = defineProps({
  invoice: { type: Object, required: true },
  students: { type: Array, default: () => [] },
  feeStructures: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  issue_date: props.invoice.issue_date ?? '',
  due_date: props.invoice.due_date ?? '',
  currency: props.invoice.currency ?? 'SAR',
  notes: props.invoice.notes ?? '',
  items: (props.invoice.items ?? []).map(item => ({
    fee_structure_id: item.fee_structure_id,
    quantity: item.quantity,
    unit_price: item.unit_price,
    discount_amount: item.discount_amount,
  })),
})

function submit() {
  form.put(`/finance/invoices/${props.invoice.id}`)
}
</script>

<template>
  <Head :title="t('finance.invoices.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.invoices.edit') }}</VCardTitle>
      <VCardSubtitle>
        {{ invoice.invoice_number }} · {{ invoice.student?.name ?? '—' }}
      </VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <InvoiceForm
          :form="form"
          :students="students"
          :fee-structures="feeStructures"
          is-edit
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        href="/finance/invoices"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn :loading="form.processing" @click="submit">
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
