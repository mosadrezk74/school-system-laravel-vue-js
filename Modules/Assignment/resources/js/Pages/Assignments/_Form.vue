<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

const props = defineProps({
  form: { type: Object, required: true },
  classes: { type: Array, default: () => [] },

  /** Real teaching assignments: class -> subject -> teacher. */
  classSubjects: { type: Array, default: () => [] },
  currentSemester: { type: Object, default: null },
})

const { t } = useI18n()

// A trailing "*" on the label is the one cue every field needs, required or
// not — leaving it off already reads as optional, so there's no separate
// "Optional" marker to maintain in sync.
const required = label => `${label} *`

const classOptions = computed(() => props.classes.map(c => ({
  title: c.name,
  value: c.id,
})))

/*
 * Subjects and teachers are narrowed from the same class_subjects data the
 * server validates against, so the form can only ever offer a combination the
 * backend will accept. This mirrors the rule; it does not enforce it.
 */
const subjectOptions = computed(() => {
  if (!props.form.class_id)
    return []

  const seen = new Map()

  for (const link of props.classSubjects) {
    if (link.class_id === props.form.class_id && !seen.has(link.subject_id))
      seen.set(link.subject_id, { title: link.subject_name, value: link.subject_id })
  }

  return [...seen.values()]
})

const teacherOptions = computed(() => {
  if (!props.form.class_id || !props.form.subject_id)
    return []

  const seen = new Map()

  for (const link of props.classSubjects) {
    if (link.class_id === props.form.class_id
      && link.subject_id === props.form.subject_id
      && link.teacher_id
      && !seen.has(link.teacher_id)) {
      seen.set(link.teacher_id, { title: link.teacher_name, value: link.teacher_id })
    }
  }

  return [...seen.values()]
})

// A subject or teacher chosen for the previous class is meaningless afterwards.
watch(() => props.form.class_id, () => {
  props.form.subject_id = null
  props.form.teacher_id = null
})

watch(() => props.form.subject_id, () => {
  props.form.teacher_id = null
})


const activeTab = ref('details')

// Field names per tab, used only to badge a tab when one of its fields has a
// validation error — the user shouldn't have to click through every tab to
// find out which one needs attention.
const tabFields = {
  details: ['title', 'description', 'class_id', 'subject_id', 'teacher_id'],
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

        <VCol cols="12">
          <VAlert
            :type="currentSemester ? 'info' : 'warning'"
            variant="tonal"
            density="compact"
          >
            {{ currentSemester
              ? `${$t('assignments.currentSemester')}: ${currentSemester.name}`
              : $t('assignments.noCurrentSemester') }}
          </VAlert>
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.class_id"
            :items="classOptions"
            :label="required(t('assignments.class'))"
            :hint="$t('assignments.hints.class')"
            persistent-hint
            :error-messages="form.errors.class_id"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.subject_id"
            :items="subjectOptions"
            :label="required(t('assignments.subject'))"
            :disabled="!form.class_id"
            :hint="!form.class_id
              ? $t('assignments.selectClassFirst')
              : (subjectOptions.length ? $t('assignments.hints.subject') : $t('assignments.noSubjectsForClass'))"
            persistent-hint
            :error-messages="form.errors.subject_id"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.teacher_id"
            :items="teacherOptions"
            :label="required(t('assignments.teacher'))"
            :disabled="!form.class_id || !form.subject_id"
            :hint="!form.subject_id
              ? $t('assignments.selectSubjectFirst')
              : (teacherOptions.length ? $t('assignments.hints.teacher') : $t('assignments.noTeacherForClassSubject'))"
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
