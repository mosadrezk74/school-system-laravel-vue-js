<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import GradeLevelForm from './_Form.vue'

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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('gradeLevels.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('gradeLevels.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <GradeLevelForm :form="form" />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
