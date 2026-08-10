<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import GradeLevelForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const { t } = useI18n()

const form = useForm({
  name: '',
  description: '',
  display_order: 0,
  is_active: true,
})

function submit() {
  form.post('/academic/grade-levels')
}
</script>

<template>
  <Head :title="t('gradeLevels.addNew')" />

  <AppPageHeader
    :title="t('gradeLevels.addNew')"
    :subtitle="t('gradeLevels.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <GradeLevelForm :form="form" />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/academic/grade-levels"
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
