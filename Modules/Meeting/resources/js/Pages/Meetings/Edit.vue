<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import MeetingForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  meeting: { type: Object, required: true },
  teachers: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  title: props.meeting.title || '',
  description: props.meeting.description || '',
  scheduled_at: props.meeting.scheduled_at ? props.meeting.scheduled_at.slice(0, 16) : null,
  duration_minutes: props.meeting.duration_minutes,
  teacher_id: props.meeting.teacher_id,
  semester_id: props.meeting.semester_id,
  school_class_id: props.meeting.school_class_id,
  subject_id: props.meeting.subject_id,
})

function submit() {
  form.put(`/meetings/meetings/${props.meeting.id}`)
}
</script>

<template>
  <Head :title="t('meetings.edit')" />

  <AppPageHeader
    :title="t('meetings.edit')"
    :subtitle="meeting.title"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <MeetingForm
          :form="form"
          :teachers="teachers"
          :semesters="semesters"
          :classes="classes"
          :subjects="subjects"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
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
        {{ t('common.update') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
