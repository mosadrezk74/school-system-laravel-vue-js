<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  /** Sent by the controller from the same enum the rules validate against. */
  recordStatuses: { type: Array, default: () => [] },
  form: { type: Object, required: true },
  students: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

const { t } = useI18n()

// A trailing "*" on the label is the one cue every field needs, required or
// not — leaving it off already reads as optional, so there's no separate
// "Optional" marker to maintain in sync.
const required = label => `${label} *`

const studentOptions = computed(() => props.students.map(student => ({
  title: student.name,
  value: student.id,
})))

// The point value is shown right in the option label (e.g. "Late to class
// (-2 pts)") so the reporter can see the impact before submitting, without
// needing a separate preview area.
const categoryOptions = computed(() => props.categories.map(category => ({
  title: `${category.name} (${category.points >= 0 ? '+' : ''}${category.points} ${category.type === 'merit' ? 'pts' : 'pts'})`,
  value: category.id,
})))

</script>

<template>
  <div class="d-flex align-center gap-3 mb-6">
    <VAvatar color="primary" variant="tonal" size="40">
      <VIcon icon="bx-shield-quarter" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ $t('behaviorRecords.sections.details') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ $t('behaviorRecords.sections.detailsDesc') }}
      </p>
    </div>
  </div>

  <VRow>
    <VCol cols="12" md="6">
      <VSelect
        v-model="form.student_id"
        :items="studentOptions"
        :label="required(t('behaviorRecords.student'))"
        :hint="$t('behaviorRecords.hints.student')"
        persistent-hint
        :error-messages="form.errors.student_id"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VSelect
        v-model="form.behavior_category_id"
        :items="categoryOptions"
        :label="required(t('behaviorRecords.category'))"
        :hint="$t('behaviorRecords.hints.category')"
        persistent-hint
        :error-messages="form.errors.behavior_category_id"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <AppDateField
        v-model="form.date"
        :label="required(t('behaviorRecords.date'))"
        :hint="$t('behaviorRecords.hints.date')"
        persistent-hint
        :error-messages="form.errors.date"
        required
      />
    </VCol>

    <VCol v-if="isEdit" cols="12" md="6">
      <VSelect
        v-model="form.status"
        :items="recordStatuses"
        :label="required(t('behaviorRecords.status'))"
        :hint="$t('behaviorRecords.hints.status')"
        persistent-hint
        :error-messages="form.errors.status"
        required
      />
    </VCol>

    <VCol cols="12">
      <VTextarea
        v-model="form.description"
        maxlength="1000"
        :counter="1000"
        :label="$t('behaviorRecords.description')"
        :hint="$t('behaviorRecords.hints.description')"
        persistent-hint
        :error-messages="form.errors.description"
        rows="3"
      />
    </VCol>
  </VRow>
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
