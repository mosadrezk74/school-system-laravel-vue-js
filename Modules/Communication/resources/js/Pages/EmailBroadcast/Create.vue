<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import AudienceSelector from '../../components/AudienceSelector.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  roles: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  subject: '',
  body: '',
  target_type: 'all',
  target_ids: [],
})

function submit() {
  form.post('/communication/email-broadcast', {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <Head :title="t('communication.emailBroadcast.title')" />

  <AppPageHeader
    :title="t('communication.emailBroadcast.title')"
    :subtitle="t('communication.emailBroadcast.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <TranslatableTextInput
          v-model="form.subject"
          :maxlength="150"
          field="subject"
          :label="t('communication.fields.subject')"
          :errors="form.errors"
          required
          class="mb-4"
        />

        <TranslatableTextarea
          v-model="form.body"
          :maxlength="5000"
          field="body"
          :label="t('communication.fields.body')"
          :errors="form.errors"
          :rows="6"
          class="mb-4"
        />

        <AudienceSelector
          v-model:target-type="form.target_type"
          v-model:target-ids="form.target_ids"
          :roles="roles"
          :academic-years="academicYears"
          :classes="classes"
          :users="users"
          :errors="form.errors"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        type="submit"
        :loading="form.processing"
        color="primary"
        @click="submit"
      >
        {{ t('communication.emailBroadcast.send') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
