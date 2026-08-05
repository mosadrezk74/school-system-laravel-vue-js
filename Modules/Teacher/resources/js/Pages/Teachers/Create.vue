<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import TeacherForm from './_Form.vue'

defineProps({
  departments: { type: Array, default: () => [] },
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
  employee_id: '',
  qualification: '',
  specialization: '',
  experience_years: 0,
  joining_date: null,
  department_id: null,
  position: '',
  salary_type: null,
  salary_base: null,
  primary_subjects: [],
})

function submit() {
  form.post('/user-management/teachers')
}
</script>

<template>
  <Head :title="t('teacher.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('teacher.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('teacher.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <TeacherForm
          :form="form"
          :departments="departments"
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
        type="submit"
        :loading="form.processing"
        color="primary"
      >
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>