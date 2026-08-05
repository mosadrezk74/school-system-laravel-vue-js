<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import TeacherForm from './_Form.vue'

const props = defineProps({
  teacher: { type: Object, required: true },
  departments: { type: Array, default: () => [] },
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
  department_id: props.teacher.department_id,
  position: props.teacher.position,
  salary_type: props.teacher.salary_type,
  salary_base: props.teacher.salary_base,
  primary_subjects: props.teacher.primary_subjects || [],
})

function submit() {
  form.put(`/user-management/teachers/${props.teacher.id}`)
}
</script>

<template>
  <Head :title="t('teacher.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('teacher.edit') }}</VCardTitle>
      <VCardSubtitle>{{ teacher.name }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <TeacherForm
          :form="form"
          :departments="departments"
          is-edit
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/user-management/teachers'"
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