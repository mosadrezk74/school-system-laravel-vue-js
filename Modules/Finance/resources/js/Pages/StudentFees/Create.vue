<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentFeeForm from './_Form.vue'

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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.studentFees.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.studentFees.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

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

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
