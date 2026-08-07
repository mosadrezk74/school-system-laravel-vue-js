<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'

defineOptions({ layout: TeacherLayout })

const props = defineProps({
  classes: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  roster: { type: Array, default: () => [] },
})

const { t } = useI18n()

const classId = ref(props.filters.class_id ?? null)
const date = ref(props.filters.date ?? new Date().toISOString().slice(0, 10))

const classOptions = computed(() => props.classes.map(c => ({ title: c.name, value: c.id })))
const statusOptions = computed(() => props.statuses.map(s => ({ title: s.label, value: s.value })))

function reload() {
  router.get('/teacher/attendance', {
    class_id: classId.value ?? '',
    date: date.value,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

watch([classId, date], reload)

const form = useForm({
  class_id: props.filters.class_id,
  date: props.filters.date,
  session: 1,
  students: props.roster.map(row => ({
    student_id: row.student_id,
    status: row.status,
    notes: row.notes,
  })),
})

watch(() => props.roster, rows => {
  form.class_id = props.filters.class_id
  form.date = props.filters.date
  form.students = rows.map(row => ({
    student_id: row.student_id,
    status: row.status,
    notes: row.notes,
  }))
})

function setAll(status) {
  form.students = form.students.map(student => ({ ...student, status }))
}

function nameFor(studentId) {
  return props.roster.find(row => row.student_id === studentId)?.name ?? '—'
}

function admissionFor(studentId) {
  return props.roster.find(row => row.student_id === studentId)?.admission_number
}

function submit() {
  form.post('/teacher/attendance', { preserveScroll: true })
}
</script>

<template>
  <Head :title="t('portal.nav.attendance')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.teacher.quickAttendance')"
        :subtitle="t('portal.teacher.attendanceSubtitle')"
        icon="bx-list-check"
      />
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap gap-4">
          <VSelect
            v-model="classId"
            :items="classOptions"
            :label="t('portal.teacher.selectClass')"
            density="compact"
            clearable
            hide-details
            style="max-inline-size: 260px; flex: 1 1 200px;"
          />

          <VTextField
            v-model="date"
            type="date"
            :label="t('portal.teacher.date')"
            density="compact"
            hide-details
            style="max-inline-size: 200px;"
          />
        </VCardText>

        <template v-if="form.students.length">
          <VDivider />

          <VCardText class="d-flex flex-wrap gap-2">
            <VBtn
              v-for="status in statusOptions"
              :key="status.value"
              variant="tonal"
              size="small"
              @click="setAll(status.value)"
            >
              {{ t('portal.teacher.markAll') }}: {{ status.title }}
            </VBtn>
          </VCardText>

          <VDivider />

          <VList density="compact">
            <VListItem
              v-for="(student, index) in form.students"
              :key="student.student_id"
            >
              <VListItemTitle>{{ nameFor(student.student_id) }}</VListItemTitle>
              <VListItemSubtitle>{{ admissionFor(student.student_id) ?? '—' }}</VListItemSubtitle>

              <template #append>
                <VBtnToggle
                  v-model="form.students[index].status"
                  density="compact"
                  variant="outlined"
                  divided
                  mandatory
                >
                  <VBtn
                    v-for="status in statusOptions"
                    :key="status.value"
                    :value="status.value"
                    size="small"
                  >
                    {{ status.title }}
                  </VBtn>
                </VBtnToggle>
              </template>
            </VListItem>
          </VList>

          <VDivider />

          <VCardText class="d-flex justify-end">
            <VBtn
              :loading="form.processing"
              prepend-icon="bx-check"
              @click="submit"
            >
              {{ t('portal.teacher.saveAttendance') }}
            </VBtn>
          </VCardText>
        </template>

        <VCardText v-else class="d-flex flex-column align-center pa-12 text-center text-disabled">
          <VIcon icon="bx-user-check" size="48" class="mb-3" />
          {{ classId ? t('portal.teacher.noStudents') : t('portal.teacher.pickClass') }}
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
