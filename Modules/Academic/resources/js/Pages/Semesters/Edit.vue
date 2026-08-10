<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SemesterForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  semester: { type: Object, required: true },
  academicYears: { type: Array, default: () => [] },
})

const { t } = useI18n()

/*
 * Seeded straight from the server prop, with one key per field the update
 * request validates — a field missing here is submitted as undefined and
 * fails validation, and a field the form binds but useForm() never declares
 * renders as an empty input.
 *
 * `name` takes the resolved single-locale string, not the name_translations
 * map: the form is one plain-text input, and the server merges the typed value
 * over the row's stored translations so untouched locales are preserved.
 *
 * The remaining `?? ''` fallbacks cover the nullable exam dates: a null would
 * make the date input uncontrolled, while an empty string is normalised back
 * to null by the DTO.
 */
const form = useForm({
  academic_year_id: props.semester.academic_year_id,
  name: props.semester.name ?? '',
  sequence: props.semester.sequence,
  start_date: props.semester.start_date,
  end_date: props.semester.end_date,
  exam_start_date: props.semester.exam_start_date ?? '',
  exam_end_date: props.semester.exam_end_date ?? '',
  is_active: props.semester.is_active,
})

function submit() {
  form.put(`/academic/semesters/${props.semester.id}`)
}
</script>

<template>
  <Head :title="t('semesters.edit')" />

  <AppPageHeader
    :title="t('semesters.edit')"
    :subtitle="semester.name"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <SemesterForm
          :form="form"
          :academic-years="academicYears"
          is-edit
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/academic/semesters"
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
    </VCardActions>
  </VCard>
</template>
