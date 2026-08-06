<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import MeetingForm from './_Form.vue'

defineProps({
  teachers: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  title: '',
  description: '',
  password: '',
  scheduled_at: null,
  duration_minutes: 45,
  teacher_id: null,
  academic_year_id: null,
  semester_id: null,
})

function submit() {
  form.post('/meetings/meetings')
}
</script>

<template>
  <Head :title="t('meetings.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('meetings.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('meetings.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <MeetingForm
          :form="form"
          :teachers="teachers"
          :academic-years="academicYears"
          :semesters="semesters"
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/meetings/meetings'"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        type="submit"
        :loading="form.processing"
        color="primary"
        @click="submit"
      >
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
