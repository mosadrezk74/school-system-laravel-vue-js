<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PaymentMethodForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  types: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  description: '',
  instructions: '',
  code: '',
  type: 'cash',
  payment_url: '',
  bank_name: '',
  account_name: '',
  account_number: '',
  iban: '',
  is_active: true,
  display_order: 0,
})

function submit() {
  form.post('/finance/payment-methods')
}
</script>

<template>
  <Head :title="t('finance.add_payment_method')" />

  <AppPageHeader
    :title="t('finance.add_payment_method')"
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
