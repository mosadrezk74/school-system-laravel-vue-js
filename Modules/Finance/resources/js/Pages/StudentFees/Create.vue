<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentFeeForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  students: { type: Array, default: () => [] },
  feeStructures: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  student_id: null,
  fee_structure_id: null,
  amount: '',
  discount_amount: '',
  status: 'pending',
})

function submit() {
  form.post('/finance/student-fees')
}
</script>

<template>
  <Head :title="t('finance.studentFees.addNew')" />

  <AppPageHeader
    :title="t('finance.studentFees.addNew')"
    :subtitle="t('finance.studentFees.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <StudentFeeForm
          :form="form"
          :students="students"
          :fee-structures="feeStructures"
          :statuses="statuses"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/finance/student-fees"
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
