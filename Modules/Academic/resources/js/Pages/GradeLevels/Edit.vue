<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import GradeLevelForm from './_Form.vue'

const props = defineProps({
  gradeLevel: { type: Object, required: true },
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
  name: props.gradeLevel.name ?? '',
  description: props.gradeLevel.description ?? '',
  display_order: props.gradeLevel.display_order ?? 0,
  is_active: props.gradeLevel.is_active,
})

function submit() {
  form.put(`/academic/grade-levels/${props.gradeLevel.id}`)
}
</script>

<template>
  <Head :title="t('gradeLevels.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('gradeLevels.edit') }}</VCardTitle>
      <VCardSubtitle>{{ gradeLevel.name }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <GradeLevelForm
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
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
