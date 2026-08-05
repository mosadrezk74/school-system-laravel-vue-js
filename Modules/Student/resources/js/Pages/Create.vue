<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

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
  academic_year_id: null,
  current_class_id: null,
  admission_date: null,
  scholarship_percentage: null,
  is_graduated: false,
})

const submit = () => {
  form.post('/user-management/students')
}
</script>

<template>
  <Head :title="$t('students.addNew')" />

  <VCard>
    <VCardTitle class="pa-4">
      {{ $t('students.addNew') }}
    </VCardTitle>

    <VCardText>
      <form @submit.prevent="submit">
        <VRow>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.name"
              :label="$t('students.name')"
              :error-messages="form.errors.name"
              required
            />
          </VCol>

          <VCol cols="12" md="6">
            <VTextField
              v-model="form.email"
              type="email"
              :label="$t('students.email')"
              :error-messages="form.errors.email"
              required
            />
          </VCol>

          <VCol cols="12" md="6">
            <VTextField
              v-model="form.username"
              :label="$t('students.username')"
              :error-messages="form.errors.username"
              required
            />
          </VCol>

          <VCol cols="12" md="6">
            <VTextField
              v-model="form.password"
              type="password"
              :label="$t('students.password')"
              :error-messages="form.errors.password"
              required
            />
          </VCol>

          <VCol cols="12" class="d-flex gap-4">
            <VBtn
              type="submit"
              color="primary"
              :loading="form.processing"
            >
              {{ $t('common.save') }}
            </VBtn>

            <VBtn
              variant="outlined"
              color="secondary"
              :tag="Link"
              href="/user-management/students"
            >
              {{ $t('common.cancel') }}
            </VBtn>
          </VCol>
        </VRow>
      </form>
    </VCardText>
  </VCard>
</template>
