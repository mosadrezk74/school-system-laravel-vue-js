<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import GuardianForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const { t } = useI18n()

const form = useForm({
  name: '',
  email: '',
  username: '',
  password: '',
  phone: '',
  national_id: '',
  is_active: true,
  occupation: '',
})

function submit() {
  form.post('/user-management/guardians')
}
</script>

<template>
  <Head :title="t('guardians.addNew')" />

  <AppPageHeader
    :title="t('guardians.addNew')"
    :subtitle="t('guardians.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <GuardianForm :form="form" />
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
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
