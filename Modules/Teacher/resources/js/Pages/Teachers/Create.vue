<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import TeacherForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  salaryTypeOptions: { type: Array, default: () => [] },
  genders: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
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
  position: '',
  salary_type: null,
  salary_base: null,
  subject_ids: [],
})

function submit() {
  form.post('/user-management/teachers')
}
</script>

<template>
  <Head :title="t('teacher.addNew')" />

  <AppPageHeader
    :title="t('teacher.addNew')"
    :subtitle="t('teacher.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <TeacherForm
          :form="form"
          :subjects="subjects"
          :salary-type-options="salaryTypeOptions"
          :genders="genders"
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
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>