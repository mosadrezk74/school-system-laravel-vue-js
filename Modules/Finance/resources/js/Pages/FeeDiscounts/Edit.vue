<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import FeeDiscountForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  feeDiscount: { type: Object, required: true },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  discountTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: props.feeDiscount.name || '',
  code: props.feeDiscount.code || '',
  discount_type: props.feeDiscount.discount_type,
  discount_value: props.feeDiscount.discount_value ?? '',
  max_amount: props.feeDiscount.max_amount ?? '',
  grade_level_id: props.feeDiscount.grade_level_id,
  classroom_id: props.feeDiscount.classroom_id,
  start_date: props.feeDiscount.start_date ?? '',
  end_date: props.feeDiscount.end_date ?? '',
  usage_limit: props.feeDiscount.usage_limit ?? '',
  is_active: props.feeDiscount.is_active,
})

function submit() {
  form.put(`/finance/fee-discounts/${props.feeDiscount.id}`)
}
</script>

<template>
  <Head :title="t('finance.feeDiscounts.edit')" />

  <AppPageHeader
    :title="t('finance.feeDiscounts.edit')"
    :subtitle="feeDiscount.name"
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
        {{ t('common.update') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
