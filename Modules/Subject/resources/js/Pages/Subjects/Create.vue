<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SubjectForm from './_Form.vue'

const { t } = useI18n()

const form = useForm({
  name: '',
  description: '',
  is_active: true,
})

function submit() {
  form.post('/academic/subjects')
}
</script>

<template>
  <Head :title="t('subject.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('subject.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('subject.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <SubjectForm :form="form" />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        href="/academic/subjects"
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
