<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'

const props = defineProps({
  form: { type: Object, required: true },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
})

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


const examTypeOptions = [
  { title: t('exams.types.midterm'), value: 'midterm' },
  { title: t('exams.types.final'), value: 'final' },
  { title: t('exams.types.quiz'), value: 'quiz' },
  { title: t('exams.types.unitTest'), value: 'unit_test' },
]

const activeTab = ref('details')

const tabFields = {
  details: ['name', 'exam_type', 'semester_id', 'class_id'],
  schedule: ['start_date', 'end_date'],
  grading: ['passing_grade', 'total_marks', 'weightage', 'is_published'],
}

const tabHasError = tab => tabFields[tab].some(field =>
  Object.keys(props.form.errors).some(key => key === field || key.startsWith(`${field}.`)))
</script>

<template>
  <VTabs
    v-model="activeTab"
    show-arrows
    class="v-tabs-pill"
  >
    <VTab value="details">
      <VIcon size="20" start icon="bx-detail" />
      {{ $t('exams.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="schedule">
      <VIcon size="20" start icon="bx-calendar" />
      {{ $t('exams.sections.schedule') }}
      <VIcon v-if="tabHasError('schedule')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="grading">
      <VIcon size="20" start icon="bx-medal" />
      {{ $t('exams.sections.grading') }}
      <VIcon v-if="tabHasError('grading')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="details">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-detail" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('exams.sections.details') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('exams.sections.detailsDesc') }}
          </p>
        </div>
      </div>

      <TranslatableTextInput
        v-model="form.name"
        field="name"
        :label="t('exams.name')"
        :errors="form.errors"
        required
        class="mb-4"
      />

      <VRow>


        <VCol cols="12" md="4">
          <VSelect
            v-model="form.exam_type"
            :items="examTypeOptions"
            :label="required(t('exams.examType'))"
            :hint="$t('exams.hints.examType')"
            persistent-hint
            :error-messages="form.errors.exam_type"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.semester_id"
            clearable
            :items="semesterOptions"
            :label="$t('exams.semester')"
            :hint="$t('exams.hints.semester')"
            persistent-hint
            :error-messages="form.errors.semester_id"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.class_id"
            clearable
            :items="classOptions"
            :label="$t('exams.class')"
            :hint="$t('exams.hints.class')"
            persistent-hint
            :error-messages="form.errors.class_id"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="schedule">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-calendar" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('exams.sections.schedule') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('exams.sections.scheduleDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.start_date"
            type="date"
            :label="required(t('exams.startDate'))"
            :hint="$t('exams.hints.startDate')"
            persistent-hint
            :error-messages="form.errors.start_date"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.end_date"
            type="date"
            :label="required(t('exams.endDate'))"
            :hint="$t('exams.hints.endDate')"
            persistent-hint
            :error-messages="form.errors.end_date"
            required
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="grading">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-medal" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('exams.sections.grading') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('exams.sections.gradingDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="4">
          <VTextField
            v-model="form.passing_grade"
            type="number"
            min="0"
            :label="$t('exams.passingGrade')"
            :hint="$t('exams.hints.passingGrade')"
            persistent-hint
            :error-messages="form.errors.passing_grade"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.total_marks"
            type="number"
            min="0"
            :label="$t('exams.totalMarks')"
            :hint="$t('exams.hints.totalMarks')"
            persistent-hint
            :error-messages="form.errors.total_marks"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.weightage"
            type="number"
            min="0"
            :label="$t('exams.weightage')"
            :hint="$t('exams.hints.weightage')"
            persistent-hint
            :error-messages="form.errors.weightage"
          />
        </VCol>

        <VCol cols="12" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_published"
            :label="$t('exams.isPublished')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('exams.hints.isPublished') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>

<style scoped>
:deep(.v-input:not(.v-input--error) .v-messages) {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.75rem;
  opacity: var(--v-medium-emphasis-opacity);
}
</style>
