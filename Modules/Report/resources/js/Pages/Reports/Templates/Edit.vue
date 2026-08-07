<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ReportTemplateForm from './_Form.vue'

const props = defineProps({
  template: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  providers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: props.template.name || '',
  code: props.template.code,
  category: props.template.category,
  query_data: props.template.query_data,
  columns: props.template.columns,
  filters: props.template.filters,
  is_active: props.template.is_active,
})

function submit() {
  form.put(`/reports/templates/${props.template.id}`)
}
</script>

<template>
  <Head :title="t('report.templates.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('report.templates.edit') }}</VCardTitle>
      <VCardSubtitle>{{ template.name }}</VCardSubtitle>
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
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
