<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentForm from './_Form.vue'

defineProps({
  classes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  email: '',
  username: '',
  password: '',
  gender: 'male',
  birth_date: null,
  national_id: '',
  phone: '',
  is_active: true,
  current_class_id: null,
  admission_number: '',
  roll_number: '',
  admission_date: null,
  scholarship_percentage: 0,
  is_graduated: false,
  father_name: '',
  father_phone: '',
  mother_name: '',
  mother_phone: '',
  guardian_name: '',
  guardian_phone: '',
  guardian_relation: '',
  address: '',
  city: '',
  blood_group: '',
  allergies: '',
  medical_conditions: '',
  previous_school: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  emergency_contact_relation: '',
})

function submit() {
  form.post('/user-management/students')
}
</script>

<template>
  <Head :title="t('students.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('students.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('students.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <StudentForm
          :form="form"
          :classes="classes"
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/user-management/students'"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        :loading="form.processing"
        @click="submit"
      >
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
