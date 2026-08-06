<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

const props = defineProps({
  form: { type: Object, required: true },
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
})

const { t } = useI18n()

// A trailing "*" on the label is the one cue every field needs, required or
// not — leaving it off already reads as optional, so there's no separate
// "Optional" marker to maintain in sync.
const required = label => `${label} *`

const filteredClasses = computed(() =>
  props.classes.filter(c => c.academic_year_id === props.form.academic_year_id))

const classOptions = computed(() => filteredClasses.value.map(c => ({
  title: [c.name, c.section_code].filter(Boolean).join(' · '),
  value: c.id,
})))

const filteredSemesters = computed(() =>
  props.semesters.filter(s => s.academic_year_id === props.form.academic_year_id))

const semesterOptions = computed(() => filteredSemesters.value.map(s => ({
  title: s.name,
  value: s.id,
})))

watch(() => props.form.academic_year_id, () => {
  if (!filteredClasses.value.some(c => c.id === props.form.class_id))
    props.form.class_id = null

  if (!filteredSemesters.value.some(s => s.id === props.form.semester_id))
    props.form.semester_id = null
})

const activeTab = ref('details')

// Field names per tab, used only to badge a tab when one of its fields has a
// validation error — the user shouldn't have to click through every tab to
// find out which one needs attention.
const tabFields = {
  details: ['title', 'description', 'class_id', 'subject_id', 'teacher_id', 'academic_year_id', 'semester_id'],
  grading: ['max_score', 'deadline', 'assignment_type', 'is_group_work'],
  publishing: ['is_published'],
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
      {{ $t('assignments.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="grading">
      <VIcon size="20" start icon="bx-medal" />
      {{ $t('assignments.sections.grading') }}
      <VIcon v-if="tabHasError('grading')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="publishing">
      <VIcon size="20" start icon="bx-globe" />
      {{ $t('assignments.sections.publishing') }}
      <VIcon v-if="tabHasError('publishing')" size="16" end icon="bx-error-circle" color="error" />
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
            {{ $t('assignments.sections.details') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('assignments.sections.detailsDesc') }}
          </p>
        </div>
      </div>

      <TranslatableTextInput
        v-model="form.title"
        field="title"
        :label="t('assignments.titleField')"
        :errors="form.errors"
        required
        class="mb-4"
      />

      <TranslatableTextarea
        v-model="form.description"
        field="description"
        :label="t('assignments.description')"
        :errors="form.errors"
        :rows="3"
        class="mb-4"
      />

      <VRow>
        <VCol cols="12" md="4">
          <VSelect
            v-model="form.academic_year_id"
            :items="academicYears.map(y => ({ title: y.name, value: y.id }))"
            :label="required(t('assignments.academicYear'))"
            :hint="$t('assignments.hints.academicYear')"
            persistent-hint
            :error-messages="form.errors.academic_year_id"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.semester_id"
            clearable
            :items="semesterOptions"
            :label="$t('assignments.semester')"
            :hint="$t('assignments.hints.semester')"
            persistent-hint
            :error-messages="form.errors.semester_id"
            :disabled="!form.academic_year_id"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.class_id"
            :items="classOptions"
            :label="required(t('assignments.class'))"
            :hint="$t('assignments.hints.class')"
            persistent-hint
            :error-messages="form.errors.class_id"
            :disabled="!form.academic_year_id"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.subject_id"
            :items="subjects.map(s => ({ title: s.name, value: s.id }))"
            :label="required(t('assignments.subject'))"
            :hint="$t('assignments.hints.subject')"
            persistent-hint
            :error-messages="form.errors.subject_id"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.teacher_id"
            :items="teachers.map(u => ({ title: u.name, value: u.id }))"
            :label="required(t('assignments.teacher'))"
            :hint="$t('assignments.hints.teacher')"
            persistent-hint
            :error-messages="form.errors.teacher_id"
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
            {{ $t('assignments.sections.grading') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('assignments.sections.gradingDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="4">
          <VTextField
            v-model="form.max_score"
            type="number"
            min="0"
            :label="required(t('assignments.maxScore'))"
            :hint="$t('assignments.hints.maxScore')"
            persistent-hint
            :error-messages="form.errors.max_score"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.deadline"
            type="datetime-local"
            :label="required(t('assignments.deadline'))"
            :hint="$t('assignments.hints.deadline')"
            persistent-hint
            :error-messages="form.errors.deadline"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.assignment_type"
            :label="$t('assignments.assignmentType')"
            :hint="$t('assignments.hints.assignmentType')"
            persistent-hint
            :error-messages="form.errors.assignment_type"
          />
        </VCol>

        <VCol cols="12" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_group_work"
            :label="$t('assignments.isGroupWork')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('assignments.hints.isGroupWork') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="publishing">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-globe" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('assignments.sections.publishing') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('assignments.sections.publishingDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_published"
            :label="$t('assignments.isPublished')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('assignments.hints.isPublished') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>

<style scoped>
/*
 * The field-level hint doubles as help text here, not a brief input format
 * note, so it needs to read clearly as secondary/quiet — smaller and greyer
 * than the label above it, matching the .text-caption/.text-medium-emphasis
 * pair already used for the switch captions in this same form.
 *
 * The template's own _field.scss colours the hint with the active theme
 * colour while a field is focused (v-messages inherits that colour), so
 * `color` has to be forced here too — opacity alone isn't enough to override
 * it back to a neutral grey. Scoped to :not(.v-input--error) so a real
 * validation message underneath still shows in the theme's error colour.
 */
:deep(.v-input:not(.v-input--error) .v-messages) {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.75rem;
  opacity: var(--v-medium-emphasis-opacity);
}
</style>
