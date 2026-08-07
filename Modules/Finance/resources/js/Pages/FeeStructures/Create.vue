<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import FeeStructureForm from './_Form.vue'

defineProps({
  feeCategories: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  frequencies: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  fee_category_id: null,
  grade_level_id: null,
  classroom_id: null,
  amount: '',
  frequency: null,
  is_optional: false,
  due_day: '',
  late_fee_amount: '',
  late_fee_days: '',
})

function submit() {
  form.post('/finance/fee-structures')
}
</script>

<template>
  <Head :title="t('finance.feeStructures.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.feeStructures.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.feeStructures.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <FeeStructureForm
          :form="form"
          :fee-categories="feeCategories"
          :grade-levels="gradeLevels"
          :classrooms="classrooms"
          :frequencies="frequencies"
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        href="/finance/fee-structures"
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
