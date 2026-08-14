<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AttendanceForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  /** Sent by the controller from the same enum the rules validate against. */
  attendanceStatuses: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  roster: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  semester_id: null,
  class_id: null,
  date: null,
  session: 1,
  students: props.roster.map(student => ({ ...student })),
})

function onClassChange(classId) {
  if (!classId)
    return

  router.get('/attendance/records/create', { class_id: classId }, {
    preserveState: true,
    preserveScroll: true,
    only: ['roster'],
    onSuccess: (page) => {
      form.students = page.props.roster.map(student => ({ ...student }))
    },
  })
}

function submit() {
  form.post('/attendance/records')
}
</script>

<template>
  <Head :title="t('attendanceRecords.addNew')" />

  <AppPageHeader
    :title="t('attendanceRecords.addNew')"
    :subtitle="t('attendanceRecords.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <AttendanceForm
          :form="form"
          :semesters="semesters"
          :classes="classes"
          :attendance-statuses="attendanceStatuses"
          @class-change="onClassChange"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/attendance/records'"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        :loading="form.processing"
        :disabled="!form.students.length"
        @click="submit"
      >
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
