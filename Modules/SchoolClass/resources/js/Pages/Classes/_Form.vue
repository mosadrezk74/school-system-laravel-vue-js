<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
  form: { type: Object, required: true },
  academicYears: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  branches: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

// A classroom belongs to exactly one branch, so the picker only ever offers
// classrooms that match the branch currently selected above it.
const filteredClassrooms = computed(() =>
  props.classrooms.filter(c => c.branch_id === props.form.branch_id))

const classroomOptions = computed(() => filteredClassrooms.value.map(c => ({
  title: c.name,
  value: c.id,
})))

watch(() => props.form.branch_id, () => {
  const stillValid = filteredClassrooms.value.some(c => c.id === props.form.classroom_id)

  if (!stillValid)
    props.form.classroom_id = null
})

const teacherOptions = computed(() => props.teachers.map(t => ({
  title: t.name,
  value: t.id,
})))
</script>

<template>
  <VRow>
    <VCol cols="12" md="4">
      <VSelect
        v-model="form.branch_id"
        :items="branches.map(b => ({ title: b.name, value: b.id }))"
        :label="$t('classes.branch')"
        :hint="$t('classes.hints.branch')"
        persistent-hint
        :error-messages="form.errors.branch_id"
        required
      />
    </VCol>

    <VCol cols="12" md="4">
      <VSelect
        v-model="form.grade_level_id"
        :items="gradeLevels.map(g => ({ title: g.name, value: g.id }))"
        :label="$t('classes.gradeLevel')"
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
        :label="$t('classes.academicYear')"
        :hint="$t('classes.hints.academicYear')"
        persistent-hint
        :error-messages="form.errors.academic_year_id"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.name"
        :label="$t('classes.name')"
        :hint="$t('classes.hints.name')"
        persistent-hint
        :error-messages="form.errors.name"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.section_code"
        :label="$t('classes.sectionCode')"
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
        :label="$t('classes.capacity')"
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
        :disabled="!form.branch_id"
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
