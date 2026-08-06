<script setup>
import { useI18n } from 'vue-i18n'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

const props = defineProps({
  form: { type: Object, required: true },
  teachers: { type: Array, default: () => [] },
  branches: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
})

const { t } = useI18n()

const required = label => `${label} *`

const filteredSemesters = () =>
  props.semesters.filter(s => s.academic_year_id === props.form.academic_year_id)
</script>

<template>
  <div class="d-flex align-center gap-3 mb-6">
    <VAvatar color="primary" variant="tonal" size="40">
      <VIcon icon="bx-video" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('meetings.sections.details') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('meetings.sections.detailsDesc') }}
      </p>
    </div>
  </div>

  <VRow>
    <VCol cols="12">
      <VTextField
        v-model="form.title"
        :label="required(t('meetings.titleField'))"
        :hint="t('meetings.hints.title')"
        persistent-hint
        :error-messages="form.errors.title"
        required
        class="mb-4"
      />
    </VCol>

    <VCol cols="12">
      <TranslatableTextarea
        v-model="form.description"
        field="description"
        :label="t('meetings.description')"
        :errors="form.errors"
        :rows="3"
      />
    </VCol>

    <VCol cols="12" md="6">
      <VSelect
        v-model="form.teacher_id"
        :items="teachers.map(u => ({ title: u.name, value: u.id }))"
        :label="required(t('meetings.teacher'))"
        :hint="t('meetings.hints.teacher')"
        persistent-hint
        :error-messages="form.errors.teacher_id"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VSelect
        v-model="form.branch_id"
        clearable
        :items="branches.map(b => ({ title: b.name, value: b.id }))"
        :label="t('meetings.branch')"
        :hint="t('meetings.hints.branch')"
        persistent-hint
        :error-messages="form.errors.branch_id"
      />
    </VCol>

    <VCol cols="12" md="4">
      <VSelect
        v-model="form.academic_year_id"
        clearable
        :items="academicYears.map(y => ({ title: y.name, value: y.id }))"
        :label="t('meetings.academicYear')"
        :hint="t('meetings.hints.academicYear')"
        persistent-hint
        :error-messages="form.errors.academic_year_id"
      />
    </VCol>

    <VCol cols="12" md="4">
      <VSelect
        v-model="form.semester_id"
        clearable
        :items="filteredSemesters().map(s => ({ title: s.name, value: s.id }))"
        :label="t('meetings.semester')"
        :hint="t('meetings.hints.semester')"
        persistent-hint
        :error-messages="form.errors.semester_id"
        :disabled="!form.academic_year_id"
      />
    </VCol>

    <VCol cols="12" md="4">
      <VTextField
        v-model="form.duration_minutes"
        type="number"
        min="1"
        :label="required(t('meetings.durationMinutes'))"
        :hint="t('meetings.hints.durationMinutes')"
        persistent-hint
        :error-messages="form.errors.duration_minutes"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.scheduled_at"
        type="datetime-local"
        :label="required(t('meetings.scheduledAt'))"
        :hint="t('meetings.hints.scheduledAt')"
        persistent-hint
        :error-messages="form.errors.scheduled_at"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.password"
        :label="t('meetings.password')"
        :hint="t('meetings.hints.password')"
        persistent-hint
        :error-messages="form.errors.password"
      />
    </VCol>
  </VRow>
</template>
