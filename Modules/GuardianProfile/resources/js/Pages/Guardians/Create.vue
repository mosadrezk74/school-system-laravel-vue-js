<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import GuardianForm from './_Form.vue'

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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('guardians.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('guardians.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <GuardianForm :form="form" />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
