<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import FeeStructureForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  feeStructure: { type: Object, required: true },
  feeCategories: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  frequencies: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: props.feeStructure.name || '',
  fee_category_id: props.feeStructure.fee_category_id,
  grade_level_id: props.feeStructure.grade_level_id,
  classroom_id: props.feeStructure.classroom_id,
  academic_year_id: props.feeStructure.academic_year_id,
  semester_id: props.feeStructure.semester_id,
  is_active: props.feeStructure.is_active ?? true,
  amount: props.feeStructure.amount ?? '',
  frequency: props.feeStructure.frequency,
  is_optional: props.feeStructure.is_optional,
  due_day: props.feeStructure.due_day ?? '',
  late_fee_amount: props.feeStructure.late_fee_amount ?? '',
  late_fee_days: props.feeStructure.late_fee_days ?? '',
})

function submit() {
  form.put(`/finance/fee-structures/${props.feeStructure.id}`)
}
</script>

<template>
  <Head :title="t('finance.feeStructures.edit')" />

  <AppPageHeader
    :title="t('finance.feeStructures.edit')"
    :subtitle="feeStructure.name"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <FeeStructureForm
          :form="form"
          :fee-categories="feeCategories"
          :grade-levels="gradeLevels"
          :classrooms="classrooms"
          :frequencies="frequencies"
          :academic-years="academicYears"
          :semesters="semesters"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/finance/fee-structures"
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
