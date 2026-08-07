<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ReportTemplateForm from './_Form.vue'

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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('report.templates.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('report.templates.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <ReportTemplateForm
          :form="form"
          :categories="categories"
          :providers="providers"
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
