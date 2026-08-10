<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import FeeDiscountForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  discountTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  code: '',
  discount_type: null,
  discount_value: '',
  max_amount: '',
  grade_level_id: null,
  classroom_id: null,
  start_date: '',
  end_date: '',
  usage_limit: '',
  is_active: true,
})

function submit() {
  form.post('/finance/fee-discounts')
}
</script>

<template>
  <Head :title="t('finance.feeDiscounts.addNew')" />

  <AppPageHeader
    :title="t('finance.feeDiscounts.addNew')"
    :subtitle="t('finance.feeDiscounts.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <FeeDiscountForm
          :form="form"
          :grade-levels="gradeLevels"
          :classrooms="classrooms"
          :discount-types="discountTypes"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/finance/fee-discounts"
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
