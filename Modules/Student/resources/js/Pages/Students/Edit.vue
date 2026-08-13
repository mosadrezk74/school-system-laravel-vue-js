<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  relationships: { type: Array, default: () => [] },
  genders: { type: Array, default: () => [] },
  student: { type: Object, required: true },
  classes: { type: Array, default: () => [] },
  bloodGroups: { type: Array, default: () => [] },
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
  current_class_id: props.student.current_class_id,
  admission_number: props.student.admission_number,
  roll_number: props.student.roll_number,
  admission_date: props.student.admission_date,
  is_graduated: props.student.is_graduated,
  scholarship_percentage: props.student.scholarship_percentage ?? 0,
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

  <AppPageHeader
    :title="t('students.edit')"
    :subtitle="student.name"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <StudentForm
          :form="form"
          :classes="classes"
          :blood-groups="bloodGroups"
          :relationships="relationships"
          :genders="genders"
          is-edit
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
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
    </VCardActions>
  </VCard>
</template>
