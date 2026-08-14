<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  /** Sent by the controller from the same enum the rules validate against. */
  attendanceStatuses: { type: Array, default: () => [] },
  form: { type: Object, required: true },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

const emit = defineEmits(['class-change'])

const { t } = useI18n()

const required = label => `${label} *`

const classOptions = computed(() => props.classes.map(c => ({
  title: c.name,
  value: c.id,
})))

const semesterOptions = computed(() => props.semesters.map(s => ({
  title: s.name,
  value: s.id,
})))

function onClassChange(value) {
  emit('class-change', value)
}
</script>

<template>
  <VRow>

    <VCol cols="12" md="4">
      <VSelect
        v-model="form.semester_id"
        clearable
        :items="semesterOptions"
        :label="t('attendanceRecords.semester')"
        :error-messages="form.errors.semester_id"
        :disabled="isEdit"
      />
    </VCol>

    <VCol cols="12" md="4">
      <VSelect
        v-model="form.class_id"
        :items="classOptions"
        :label="required(t('attendanceRecords.class'))"
        :error-messages="form.errors.class_id"
        :disabled="isEdit"
        required
        @update:model-value="onClassChange"
      />
    </VCol>

    <VCol cols="12" md="4">
      <AppDateField
        v-model="form.date"
        :label="required(t('attendanceRecords.date'))"
        :error-messages="form.errors.date"
        :disabled="isEdit"
        required
      />
    </VCol>

    <VCol cols="12" md="4">
      <VTextField
        v-model="form.session"
        inputmode="numeric"
        max="20"
        step="1"
        type="number"
        min="1"
        :label="required(t('attendanceRecords.session'))"
        :error-messages="form.errors.session"
        :disabled="isEdit"
        required
      />
    </VCol>
  </VRow>

  <VDivider class="my-6" />

  <div v-if="!form.class_id" class="text-disabled text-center pa-8">
    {{ t('attendanceRecords.selectClassHint') }}
  </div>

  <VTable v-else-if="form.students.length">
    <thead>
      <tr>
        <th>{{ t('attendanceRecords.student') }}</th>
        <th>{{ t('attendanceRecords.admissionNumber') }}</th>
        <th>{{ t('attendanceRecords.status') }}</th>
        <th>{{ t('attendanceRecords.checkInTime') }}</th>
        <th>{{ t('attendanceRecords.checkOutTime') }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(row, index) in form.students" :key="row.student_id">
        <td>{{ row.name }}</td>
        <td>{{ row.admission_number ?? '—' }}</td>
        <td>
          <VSelect
            v-model="form.students[index].status"
            :items="attendanceStatuses"
            density="compact"
            hide-details
            style="max-inline-size: 160px;"
          />
        </td>
        <td>
          <VTextField
            v-model="form.students[index].check_in_time"
            type="time"
            density="compact"
            hide-details
            style="max-inline-size: 140px;"
          />
        </td>
        <td>
          <VTextField
            v-model="form.students[index].check_out_time"
            type="time"
            density="compact"
            hide-details
            style="max-inline-size: 140px;"
          />
        </td>
      </tr>
    </tbody>
  </VTable>

  <div v-else class="text-disabled text-center pa-8">
    {{ t('attendanceRecords.noStudentsInClass') }}
  </div>
</template>
