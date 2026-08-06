<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  classes: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
  roster: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const classId = ref(props.filters.class_id ?? null)
const date = ref(props.filters.date ?? new Date().toISOString().slice(0, 10))

const form = useForm({
  class_id: props.filters.class_id ?? null,
  date: props.filters.date ?? date.value,
  session: 1,
  students: [],
})

const selectedClass = computed(() =>
  props.classes.find(c => c.id === classId.value) ?? null)

const classOptions = computed(() =>
  props.classes.map(c => ({
    title: [c.name, c.section_code].filter(Boolean).join(' · '),
    value: c.id,
  })))

const statusOptions = computed(() =>
  props.statuses.map(s => ({ title: s.label, value: s.value })))

function syncForm() {
  form.class_id = classId.value
  form.date = date.value
  form.students = props.roster.map(student => ({
    student_id: student.student_id,
    status: student.status,
    notes: student.notes ?? '',
  }))
}

syncForm()

watch(() => props.roster, syncForm)

function reload() {
  if (!classId.value) {
    return
  }

  router.get('/attendance/take', { class_id: classId.value, date: date.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

watch([classId, date], reload)

function markAllPresent() {
  form.students = form.students.map(student => ({ ...student, status: 'present' }))
}

function studentName(index) {
  return props.roster[index]?.name ?? '—'
}

function admissionNumber(index) {
  return props.roster[index]?.admission_number ?? '—'
}

function submit() {
  form.post('/attendance/take', { preserveScroll: true })
}
</script>

<template>
  <Head :title="t('attendanceTake.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('attendanceTake.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('attendanceTake.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VSelect
        v-model="classId"
        :label="t('attendanceRecords.class')"
        :items="classOptions"
        :error-messages="form.errors.class_id"
        density="compact"
        style="max-inline-size: 260px;"
      />

      <VTextField
        v-model="date"
        type="date"
        :label="t('attendanceRecords.date')"
        :error-messages="form.errors.date"
        density="compact"
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDivider />

    <VCardText v-if="!classId" class="text-medium-emphasis">
      {{ t('attendanceTake.selectClass') }}
    </VCardText>

    <VCardText v-else-if="!roster.length" class="text-medium-emphasis">
      {{ t('attendanceTake.emptyRoster') }}
    </VCardText>

    <template v-else>
      <VCardText class="d-flex justify-end">
        <VBtn
          variant="tonal"
          prepend-icon="bx-check-double"
          @click="markAllPresent"
        >
          {{ t('attendanceTake.markAllPresent') }}
        </VBtn>
      </VCardText>

      <VTable>
        <thead>
          <tr>
            <th>{{ t('attendanceRecords.student') }}</th>
            <th>{{ t('attendanceRecords.admissionNumber') }}</th>
            <th style="inline-size: 220px;">
              {{ t('attendanceRecords.status') }}
            </th>
            <th>{{ t('attendanceTake.notes') }}</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="(student, index) in form.students"
            :key="student.student_id"
          >
            <td>{{ studentName(index) }}</td>
            <td>{{ admissionNumber(index) }}</td>
            <td>
              <VSelect
                v-model="student.status"
                :items="statusOptions"
                :error-messages="form.errors[`students.${index}.status`]"
                density="compact"
                hide-details="auto"
              />
            </td>
            <td>
              <VTextField
                v-model="student.notes"
                :error-messages="form.errors[`students.${index}.notes`]"
                density="compact"
                hide-details="auto"
              />
            </td>
          </tr>
        </tbody>
      </VTable>

      <VDivider />

      <VCardText class="d-flex justify-end">
        <VBtn
          :loading="form.processing"
          :disabled="form.processing"
          @click="submit"
        >
          {{ t('attendanceTake.save') }}
        </VBtn>
      </VCardText>
    </template>
  </VCard>
</template>
