<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SchoolTermForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  term: { type: Object, required: true },
})

const { t } = useI18n()

const form = useForm({
  title: props.term.title ?? '',
  content: props.term.content ?? '',
})

function submit() {
  form.put(`/settings/school-terms/${props.term.id}`)
}
</script>

<template>
  <Head :title="t('schoolTerms.editDraft')" />

  <AppPageHeader
    :title="t('schoolTerms.editDraft')"
    :subtitle="t('schoolTerms.versionLabel', { version: term.version })"
  />

  <VCard class="app-form-card">
    <VCardText>
      <form @submit.prevent="submit">
        <SchoolTermForm :form="form" />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/settings/school-terms"
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
        {{ t('common.update') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
