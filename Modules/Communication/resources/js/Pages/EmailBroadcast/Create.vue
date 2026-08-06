<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import AudienceSelector from '../../components/AudienceSelector.vue'

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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('communication.emailBroadcast.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('communication.emailBroadcast.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <TranslatableTextInput
          v-model="form.subject"
          field="subject"
          :label="t('communication.fields.subject')"
          :errors="form.errors"
          required
          class="mb-4"
        />

        <TranslatableTextarea
          v-model="form.body"
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

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        type="submit"
        :loading="form.processing"
        color="primary"
        @click="submit"
      >
        {{ t('communication.emailBroadcast.send') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
