<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  form: { type: Object, required: true },
  academicYears: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

const { t } = useI18n()

// A trailing "*" on the label is the one cue every field needs, required or
// not — leaving it off already reads as optional, so there's no separate
// "Optional" marker to maintain in sync.
const required = label => `${label} *`

const classroomOptions = computed(() => props.classrooms.map(c => ({
  title: c.name,
  value: c.id,
})))

const teacherOptions = computed(() => props.teachers.map(t => ({
  title: t.name,
  value: t.id,
})))

const activeTab = ref('details')

const tabFields = {
  details: ['grade_level_id', 'academic_year_id', 'name', 'section_code', 'capacity', 'classroom_id', 'is_active'],
  staffing: ['class_teacher_id', 'assistant_teacher_id'],
}

const tabHasError = tab => tabFields[tab].some(field => !!props.form.errors[field])
</script>

<template>
  <VTabs
    v-model="activeTab"
    show-arrows
    class="v-tabs-pill"
  >
    <VTab value="details">
      <VIcon size="20" start icon="bx-chalkboard" />
      {{ $t('classes.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="staffing">
      <VIcon size="20" start icon="bx-user-voice" />
      {{ $t('classes.sections.staffing') }}
      <VIcon v-if="tabHasError('staffing')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="details">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-chalkboard" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('classes.sections.details') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('classes.sections.detailsDesc') }}
          </p>
        </div>
      </div>

      <VRow>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.grade_level_id"
            :items="gradeLevels.map(g => ({ title: g.name, value: g.id }))"
            :label="required(t('classes.gradeLevel'))"
            :hint="$t('classes.hints.gradeLevel')"
            persistent-hint
            :error-messages="form.errors.grade_level_id"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.academic_year_id"
            :items="academicYears.map(y => ({ title: y.name, value: y.id }))"
            :label="required(t('classes.academicYear'))"
            :hint="$t('classes.hints.academicYear')"
            persistent-hint
            :error-messages="form.errors.academic_year_id"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.name"
            :label="required(t('classes.name'))"
            :hint="$t('classes.hints.name')"
            persistent-hint
            :error-messages="form.errors.name"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.section_code"
            :label="required(t('classes.sectionCode'))"
            :hint="$t('classes.hints.sectionCode')"
            persistent-hint
            :error-messages="form.errors.section_code"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.capacity"
            type="number"
            min="1"
            :label="required(t('classes.capacity'))"
            :hint="$t('classes.hints.capacity')"
            persistent-hint
            :error-messages="form.errors.capacity"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.classroom_id"
            clearable
            :items="classroomOptions"
            :label="$t('classes.classroom')"
            :hint="$t('classes.hints.classroom')"
            persistent-hint
            :error-messages="form.errors.classroom_id"
          />
        </VCol>

        <VCol cols="12" md="4" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_active"
            :label="$t('classes.active')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('classes.hints.active') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="staffing">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-user-voice" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('classes.sections.staffing') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('classes.sections.staffingDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VSelect
            v-model="form.class_teacher_id"
            clearable
            :items="teacherOptions"
            :label="$t('classes.classTeacher')"
            :hint="$t('classes.hints.classTeacher')"
            persistent-hint
            :error-messages="form.errors.class_teacher_id"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.assistant_teacher_id"
            clearable
            :items="teacherOptions"
            :label="$t('classes.assistantTeacher')"
            :hint="$t('classes.hints.assistantTeacher')"
            persistent-hint
            :error-messages="form.errors.assistant_teacher_id"
          />
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
 * pair already used for the switch caption in this same form.
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
