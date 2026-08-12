<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SchoolTermForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  /** The version in force, offered as a starting point for the revision. */
  currentTerm: { type: Object, default: null },
})

const { t } = useI18n()

const form = useForm({
  title: props.currentTerm?.title ?? '',
  content: props.currentTerm?.content ?? '',
})

function submit() {
  form.post('/settings/school-terms')
}
</script>

<template>
  <Head :title="t('schoolTerms.newVersion')" />

  <AppPageHeader
    :title="t('schoolTerms.newVersion')"
    :subtitle="t('schoolTerms.subtitle')"
  />

  <VAlert
    v-if="currentTerm"
    type="info"
    variant="tonal"
    class="mb-4"
  >
    {{ t('schoolTerms.prefilledFrom', { version: currentTerm.version }) }}
  </VAlert>

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
        {{ t('schoolTerms.saveDraft') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
