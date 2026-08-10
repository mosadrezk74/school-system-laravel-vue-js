<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import GuardianForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  guardian: { type: Object, required: true },
})

const { t } = useI18n()

const form = useForm({
  name: props.guardian.name,
  email: props.guardian.email,
  username: props.guardian.username,
  password: '',
  phone: props.guardian.phone,
  national_id: props.guardian.national_id,
  is_active: props.guardian.is_active,
  occupation: props.guardian.occupation,
})

function submit() {
  form.put(`/user-management/guardians/${props.guardian.id}`)
}
</script>

<template>
  <Head :title="t('guardians.edit')" />

  <AppPageHeader
    :title="t('guardians.edit')"
    :subtitle="guardian.name"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <GuardianForm :form="form" is-edit />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/user-management/guardians'"
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
