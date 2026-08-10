<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SubjectForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const { t } = useI18n()

const form = useForm({
  name: '',
  description: '',
  is_active: true,
})

function submit() {
  form.post('/academic/subjects')
}
</script>

<template>
  <Head :title="t('subject.addNew')" />

  <AppPageHeader
    :title="t('subject.addNew')"
    :subtitle="t('subject.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <SubjectForm :form="form" />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/academic/subjects"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        :loading="form.processing"
        :disabled="form.processing"
        color="primary"
        @click="submit"
      >
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
