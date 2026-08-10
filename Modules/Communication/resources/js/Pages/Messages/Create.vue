<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
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
  title: '',
  body: '',
  target_type: 'all',
  target_ids: [],
})

function submit() {
  form.post('/communication/messages')
}
</script>

<template>
  <Head :title="t('communication.messages.addNew')" />

  <AppPageHeader
    :title="t('communication.messages.addNew')"
    :subtitle="t('communication.messages.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <TranslatableTextInput
          v-model="form.title"
          field="title"
          :label="t('communication.fields.title')"
          :errors="form.errors"
          required
          class="mb-4"
        />

        <TranslatableTextarea
          v-model="form.body"
          field="body"
          :label="t('communication.fields.body')"
          :errors="form.errors"
          :rows="5"
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
        variant="tonal"
        color="secondary"
        :href="'/communication/messages'"
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
        {{ t('communication.messages.send') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
