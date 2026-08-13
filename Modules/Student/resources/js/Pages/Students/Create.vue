<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import StudentForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  relationships: { type: Array, default: () => [] },
  genders: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  bloodGroups: { type: Array, default: () => [] },
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
  is_graduated: false,
  scholarship_percentage: 0,
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

  <AppPageHeader
    :title="t('students.addNew')"
    :subtitle="t('students.subtitle')"
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
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
