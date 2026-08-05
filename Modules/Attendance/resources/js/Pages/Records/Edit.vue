<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AttendanceForm from './_Form.vue'

const props = defineProps({
  record: { type: Object, required: true },
  roster: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  academic_year_id: props.record.academic_year_id,
  semester_id: props.record.semester_id,
  class_id: props.record.class_id,
  subject_id: props.record.subject_id,
  date: props.record.date,
  session: props.record.session,
  students: props.roster.map(student => ({ ...student })),
})

function submit() {
  form.put(`/attendance/records/${props.record.id}`)
}
</script>

<template>
  <Head :title="t('attendanceRecords.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('attendanceRecords.edit') }}</VCardTitle>
      <VCardSubtitle>
        {{ record.class ? `${record.class.name} ${record.class.section_code}` : '—' }} · {{ record.date }}
      </VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <AttendanceForm
          :form="form"
          :academic-years="academicYears"
          :semesters="semesters"
          :classes="classes"
          :subjects="subjects"
          is-edit
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
        @click="submit"
      >
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
