<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PaymentMethodForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  paymentMethod: { type: Object, required: true },
  types: { type: Array, default: () => [] },
})

const { t } = useI18n()

const record = props.paymentMethod.data ?? props.paymentMethod

const form = useForm({
  name: record.name ?? '',
  description: record.description ?? '',
  instructions: record.instructions ?? '',
  code: record.code ?? '',
  type: record.type ?? 'cash',
  payment_url: record.payment_url ?? '',
  bank_name: record.bank_name ?? '',
  account_name: record.account_name ?? '',
  account_number: record.account_number ?? '',
  iban: record.iban ?? '',
  is_active: Boolean(record.is_active),
  display_order: record.display_order ?? 0,
})

function submit() {
  form.put(`/finance/payment-methods/${record.id}`)
}
</script>

<template>
  <Head :title="t('finance.payment_methods_title')" />

  <AppPageHeader
    :title="t('finance.payment_methods_title')"
    :subtitle="t('finance.payment_methods_subtitle')"
  />

  <VCard class="app-form-card">
    <VCardText>
      <form @submit.prevent="submit">
        <PaymentMethodForm :form="form" :types="types" />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/finance/payment-methods"
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
