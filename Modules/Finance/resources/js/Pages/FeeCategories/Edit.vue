<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import FeeCategoryForm from './_Form.vue'

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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.feeCategories.edit') }}</VCardTitle>
      <VCardSubtitle>{{ feeCategory.name }}</VCardSubtitle>
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
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
