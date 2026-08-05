<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentForm from './_Form.vue'

const props = defineProps({
  student: { type: Object, required: true },
  academicYears: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: props.student.name,
  email: props.student.email,
  username: props.student.username,
  password: '',
  gender: props.student.gender,
  birth_date: props.student.birth_date,
  national_id: props.student.national_id,
  phone: props.student.phone,
  is_active: props.student.is_active,
  academic_year_id: props.student.academic_year_id,
  current_class_id: props.student.current_class_id,
  admission_number: props.student.admission_number,
  roll_number: props.student.roll_number,
  admission_date: props.student.admission_date,
  scholarship_percentage: props.student.scholarship_percentage,
  is_graduated: props.student.is_graduated,
  father_name: props.student.father_name,
  father_phone: props.student.father_phone,
  mother_name: props.student.mother_name,
  mother_phone: props.student.mother_phone,
  guardian_name: props.student.guardian_name,
  guardian_phone: props.student.guardian_phone,
  guardian_relation: props.student.guardian_relation,
  address: props.student.address,
  city: props.student.city,
  blood_group: props.student.blood_group,
  allergies: props.student.allergies,
  medical_conditions: props.student.medical_conditions,
  previous_school: props.student.previous_school,
  emergency_contact_name: props.student.emergency_contact_name,
  emergency_contact_phone: props.student.emergency_contact_phone,
  emergency_contact_relation: props.student.emergency_contact_relation,
})

function submit() {
  form.put(`/user-management/students/${props.student.id}`)
}
</script>

<template>
  <Head :title="t('students.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('students.edit') }}</VCardTitle>
      <VCardSubtitle>{{ student.name }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <StudentForm
          :form="form"
          :academic-years="academicYears"
          :classes="classes"
          is-edit
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
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
