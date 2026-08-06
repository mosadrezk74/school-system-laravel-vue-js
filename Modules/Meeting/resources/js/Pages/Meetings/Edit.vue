<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import MeetingForm from './_Form.vue'

const props = defineProps({
  meeting: { type: Object, required: true },
  teachers: { type: Array, default: () => [] },
  branches: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  title: props.meeting.title || '',
  description: props.meeting.description || '',
  password: '',
  scheduled_at: props.meeting.scheduled_at ? props.meeting.scheduled_at.slice(0, 16) : null,
  duration_minutes: props.meeting.duration_minutes,
  teacher_id: props.meeting.teacher_id,
  branch_id: props.meeting.branch_id,
  academic_year_id: props.meeting.academic_year_id,
  semester_id: props.meeting.semester_id,
})

function submit() {
  form.put(`/meetings/meetings/${props.meeting.id}`)
}
</script>

<template>
  <Head :title="t('meetings.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('meetings.edit') }}</VCardTitle>
      <VCardSubtitle>{{ meeting.title }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <MeetingForm
          :form="form"
          :teachers="teachers"
          :branches="branches"
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
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
