<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SemesterForm from './_Form.vue'

const props = defineProps({
  academicYears: { type: Array, default: () => [] },
})

const { t } = useI18n()

// Defaults to the most recent active year so the common case needs no picking.
const form = useForm({
  academic_year_id: props.academicYears[0]?.id ?? null,
  name: '',
  sequence: 1,
  start_date: '',
  end_date: '',
  exam_start_date: '',
  exam_end_date: '',
  is_active: true,
})

function submit() {
  form.post('/academic/semesters')
}
</script>

<template>
  <Head :title="t('semesters.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('semesters.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('semesters.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <SemesterForm
          :form="form"
          :academic-years="academicYears"
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
