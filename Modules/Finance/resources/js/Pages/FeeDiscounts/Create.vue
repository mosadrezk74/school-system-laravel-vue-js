<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import FeeDiscountForm from './_Form.vue'

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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.feeDiscounts.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.feeDiscounts.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

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

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
