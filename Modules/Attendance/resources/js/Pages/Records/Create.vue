<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AttendanceForm from './_Form.vue'

const props = defineProps({
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
  roster: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  semester_id: null,
  class_id: null,
  subject_id: null,
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('attendanceRecords.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('attendanceRecords.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <AttendanceForm
          :form="form"
          :semesters="semesters"
          :classes="classes"
          :subjects="subjects"
          @class-change="onClassChange"
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
