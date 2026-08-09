<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  exam: { type: Object, required: true },
  examSubject: { type: Object, required: true },
  roster: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  exam_id: props.exam.id,
  subject_id: props.examSubject.subject_id,
  class_id: props.examSubject.class_id,
  results: props.roster.map(student => ({
    student_id: student.student_id,
    obtained_marks: student.obtained_marks,
    remarks: student.remarks,
  })),
})

function submit() {
  form.post(`/examination/exams/${props.exam.id}/subjects/${props.examSubject.id}/results`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('exams.enterResults')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('exams.enterResults') }}</VCardTitle>
      <VCardSubtitle>
        {{ exam.name }} · {{ examSubject.subject?.name }} · {{ examSubject.class?.name }}
      </VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText v-if="roster.length === 0" class="text-disabled">
      {{ t('exams.noStudents') }}
    </VCardText>

    <VTable v-else>
      <thead>
        <tr>
          <th>{{ t('exams.student') }}</th>
          <th>{{ t('exams.admissionNumber') }}</th>
          <th>{{ t('exams.obtainedMarks') }}</th>
          <th>{{ t('exams.remarks') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(student, index) in roster" :key="student.student_id">
          <td>{{ student.name ?? '—' }}</td>
          <td>{{ student.admission_number ?? '—' }}</td>
          <td style="min-inline-size: 140px;">
            <VTextField
              v-model="form.results[index].obtained_marks"
              type="number"
              min="0"
              :max="examSubject.total_marks"
              density="compact"
              hide-details
              :error-messages="form.errors[`results.${index}.obtained_marks`]"
            />
          </td>
          <td style="min-inline-size: 220px;">
            <VTextField
              v-model="form.results[index].remarks"
              density="compact"
              hide-details
            />
          </td>
        </tr>
      </tbody>
    </VTable>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        :href="`/examination/exams/${exam.id}`"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn :loading="form.processing" @click="submit">
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
