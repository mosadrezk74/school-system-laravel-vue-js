<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SubjectForm from './_Form.vue'

const props = defineProps({
  subject: { type: Object, required: true },
})

const { t } = useI18n()

/*
 * Seeded straight from the server prop, one key per validated field.
 *
 * `name`/`description` take the resolved single-locale string, not the
 * *_translations map: the form is one plain-text input, and the server merges
 * the typed value over the row's stored translations, so locales the user did
 * not touch are preserved.
 */
const form = useForm({
  name: props.subject.name ?? '',
  description: props.subject.description ?? '',
  is_active: props.subject.is_active,
})

function submit() {
  form.put(`/academic/subjects/${props.subject.id}`)
}
</script>

<template>
  <Head :title="t('subject.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('subject.edit') }}</VCardTitle>
      <VCardSubtitle>{{ subject.name }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <SubjectForm
          :form="form"
          is-edit
        />
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
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
