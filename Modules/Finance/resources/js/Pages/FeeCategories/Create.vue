<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import FeeCategoryForm from './_Form.vue'

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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.feeCategories.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.feeCategories.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <FeeCategoryForm :form="form" />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
