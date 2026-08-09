<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'

const props = defineProps({
  form: { type: Object, required: true },
  academicYears: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

const { t } = useI18n()

// A trailing "*" on the label is the one cue every field needs, required or
// not — leaving it off already reads as optional.
const required = label => `${label} *`

const academicYearOptions = computed(() => props.academicYears.map(year => ({
  title: year.name,
  value: year.id,
})))
</script>

<template>
  <div class="d-flex align-center gap-3 mb-6">
    <VAvatar color="primary" variant="tonal" size="40">
      <VIcon icon="bx-calendar-event" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('semesters.sections.details') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('semesters.sections.detailsDesc') }}
      </p>
    </div>
  </div>

  <VRow>
    <VCol cols="12" md="6">
      <VSelect
        v-model="form.academic_year_id"
        :items="academicYearOptions"
        :label="required(t('semesters.fields.academicYear'))"
        :hint="t('semesters.hints.academicYear')"
        persistent-hint
        :error-messages="form.errors.academic_year_id"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <!--
        One plain-text input: the server detects which language was typed and
        fills in the other active locales, so there is no per-locale tab here.
      -->
      <TranslatableTextInput
        v-model="form.name"
        field="name"
        :label="t('semesters.fields.name')"
        :errors="form.errors"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.sequence"
        type="number"
        min="1"
        :label="required(t('semesters.fields.sequence'))"
        :hint="t('semesters.hints.sequence')"
        persistent-hint
        :error-messages="form.errors.sequence"
        required
      />
    </VCol>

    <VCol cols="12" md="6" class="d-flex flex-column justify-center">
      <VSwitch
        v-model="form.is_active"
        :label="t('semesters.fields.isActive')"
      />
      <p class="text-caption text-medium-emphasis mb-0">
        {{ t('semesters.hints.isActive') }}
      </p>
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.start_date"
        type="date"
        :label="required(t('semesters.fields.startDate'))"
        :hint="t('semesters.hints.startDate')"
        persistent-hint
        :error-messages="form.errors.start_date"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.end_date"
        type="date"
        :label="required(t('semesters.fields.endDate'))"
        :hint="t('semesters.hints.endDate')"
        persistent-hint
        :error-messages="form.errors.end_date"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.exam_start_date"
        type="date"
        clearable
        :label="t('semesters.fields.examStartDate')"
        :hint="t('semesters.hints.examStartDate')"
        persistent-hint
        :error-messages="form.errors.exam_start_date"
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.exam_end_date"
        type="date"
        clearable
        :label="t('semesters.fields.examEndDate')"
        :hint="t('semesters.hints.examEndDate')"
        persistent-hint
        :error-messages="form.errors.exam_end_date"
      />
    </VCol>
  </VRow>
</template>

<style scoped>
/*
 * The field-level hint doubles as help text here, so it needs to read as
 * secondary. The template's _field.scss colours .v-messages with the active
 * theme colour while focused, so `color` has to be forced back to neutral;
 * :not(.v-input--error) keeps real validation messages in the error colour.
 */
:deep(.v-input:not(.v-input--error) .v-messages) {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.75rem;
  opacity: var(--v-medium-emphasis-opacity);
}
</style>
