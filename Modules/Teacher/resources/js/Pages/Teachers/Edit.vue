<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import TeacherForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  salaryTypeOptions: { type: Array, default: () => [] },
  genders: { type: Array, default: () => [] },
  teacher: { type: Object, required: true },
  subjects: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: props.teacher.name,
  email: props.teacher.email,
  username: props.teacher.username,
  password: '',
  gender: props.teacher.gender,
  birth_date: props.teacher.birth_date,
  national_id: props.teacher.national_id,
  phone: props.teacher.phone,
  is_active: props.teacher.is_active,
  employee_id: props.teacher.employee_id,
  qualification: props.teacher.qualification,
  specialization: props.teacher.specialization,
  experience_years: props.teacher.experience_years,
  joining_date: props.teacher.joining_date,
  position: props.teacher.position,
  salary_type: props.teacher.salary_type,
  salary_base: props.teacher.salary_base,
  subject_ids: props.teacher.subject_ids || [],
})

function submit() {
  form.put(`/user-management/teachers/${props.teacher.id}`)
}
</script>

<template>
  <Head :title="t('teacher.edit')" />

  <AppPageHeader
    :title="t('teacher.edit')"
    :subtitle="teacher.name"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <TeacherForm
          :form="form"
          :subjects="subjects"
          :salary-type-options="salaryTypeOptions"
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
        :href="'/user-management/teachers'"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        type="submit"
        :loading="form.processing"
        color="primary"
        @click="submit"
      >
        {{ t('common.update') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>