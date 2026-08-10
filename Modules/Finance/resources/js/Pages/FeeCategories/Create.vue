<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import FeeCategoryForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const { t } = useI18n()

const form = useForm({
  name: '',
  description: '',
  code: '',
  is_active: true,
})

function submit() {
  form.post('/finance/fee-categories')
}
</script>

<template>
  <Head :title="t('finance.feeCategories.addNew')" />

  <AppPageHeader
    :title="t('finance.feeCategories.addNew')"
    :subtitle="t('finance.feeCategories.subtitle')"
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
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
