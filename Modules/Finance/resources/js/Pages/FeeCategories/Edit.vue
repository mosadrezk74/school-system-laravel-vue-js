<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import FeeCategoryForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  feeCategory: { type: Object, required: true },
})

const { t } = useI18n()

const form = useForm({
  name: props.feeCategory.name || '',
  description: props.feeCategory.description || '',
  code: props.feeCategory.code || '',
  is_active: props.feeCategory.is_active,
})

function submit() {
  form.put(`/finance/fee-categories/${props.feeCategory.id}`)
}
</script>

<template>
  <Head :title="t('finance.feeCategories.edit')" />

  <AppPageHeader
    :title="t('finance.feeCategories.edit')"
    :subtitle="feeCategory.name"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <FeeCategoryForm :form="form" />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/finance/fee-categories"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn :loading="form.processing" @click="submit">
        {{ t('common.update') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
