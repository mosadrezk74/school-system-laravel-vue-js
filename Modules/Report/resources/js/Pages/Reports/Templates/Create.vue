<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ReportTemplateForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  categories: { type: Array, default: () => [] },
  providers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  code: null,
  category: null,
  query_data: null,
  columns: null,
  filters: null,
  is_active: true,
})

function submit() {
  form.post('/reports/templates')
}
</script>

<template>
  <Head :title="t('report.templates.addNew')" />

  <AppPageHeader
    :title="t('report.templates.addNew')"
    :subtitle="t('report.templates.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <ReportTemplateForm
          :form="form"
          :categories="categories"
          :providers="providers"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/reports/templates"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn :loading="form.processing" @click="submit">
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
