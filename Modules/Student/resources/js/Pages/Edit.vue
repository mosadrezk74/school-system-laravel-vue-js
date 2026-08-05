<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  student: { type: Object, required: true },
})

const form = useForm({
  name: props.student.data?.name ?? '',
  email: props.student.data?.email ?? '',
  username: props.student.data?.username ?? '',
  password: '',
  gender: props.student.data?.gender ?? 'male',
  is_active: props.student.data?.is_active ?? true,
})

const submit = () => {
  form.put(`/user-management/students/${props.student.data.id}`)
}
</script>

<template>
  <Head :title="$t('students.edit')" />

  <VCard>
    <VCardTitle class="pa-4">
      {{ $t('students.edit') }}
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

          <VCol cols="12" class="d-flex gap-4">
            <VBtn
              type="submit"
              color="primary"
              :loading="form.processing"
            >
              {{ $t('common.update') }}
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
