<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentFeeForm from './_Form.vue'

const props = defineProps({
  studentFee: { type: Object, required: true },
  students: { type: Array, default: () => [] },
  feeStructures: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  amount: props.studentFee.amount ?? '',
  discount_amount: props.studentFee.discount_amount ?? '',
  status: props.studentFee.status,
})

function submit() {
  form.put(`/finance/student-fees/${props.studentFee.id}`)
}
</script>

<template>
  <Head :title="t('finance.studentFees.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.studentFees.edit') }}</VCardTitle>
      <VCardSubtitle>
        {{ studentFee.student?.name ?? '—' }} · {{ studentFee.fee_structure?.name ?? '—' }}
      </VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <StudentFeeForm
          :form="form"
          :students="students"
          :fee-structures="feeStructures"
          :statuses="statuses"
          is-edit
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
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
