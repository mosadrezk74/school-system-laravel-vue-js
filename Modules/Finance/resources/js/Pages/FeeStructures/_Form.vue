<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'

const props = defineProps({
  form: { type: Object, required: true },
  feeCategories: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  frequencies: { type: Array, default: () => [] },
})

const { t } = useI18n()

const required = label => `${label} *`

const feeCategoryOptions = computed(() => props.feeCategories.map(c => ({ title: c.name, value: c.id })))
const gradeLevelOptions = computed(() => props.gradeLevels.map(g => ({ title: g.name, value: g.id })))
const classroomOptions = computed(() => props.classrooms.map(c => ({ title: c.name, value: c.id })))
const frequencyOptions = computed(() => props.frequencies.map(f => ({ title: f.label, value: f.value })))

/*
 * A structure targets a grade level or a single classroom, never both — the
 * Action rejects the combination outright, so picking one clears the other
 * here rather than letting the user submit something that cannot be saved.
 */
watch(() => props.form.grade_level_id, value => {
  if (value)
    props.form.classroom_id = null
})

watch(() => props.form.classroom_id, value => {
  if (value)
    props.form.grade_level_id = null
})

const activeTab = ref('details')

const tabFields = {
  details: ['name', 'fee_category_id', 'grade_level_id', 'classroom_id'],
  amount: ['amount', 'frequency', 'is_optional', 'due_day'],
  late: ['late_fee_amount', 'late_fee_days'],
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
      {{ t('finance.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="amount">
      <VIcon size="20" start icon="bx-money" />
      {{ t('finance.sections.amount') }}
      <VIcon v-if="tabHasError('amount')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="late">
      <VIcon size="20" start icon="bx-time-five" />
      {{ t('finance.sections.lateFee') }}
      <VIcon v-if="tabHasError('late')" size="16" end icon="bx-error-circle" color="error" />
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
            {{ t('finance.sections.details') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('finance.feeStructures.sectionDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <TranslatableTextInput
            v-model="form.name"
            field="name"
            :label="t('finance.fields.name')"
            :errors="form.errors"
            required
          />
          <p class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('finance.hints.structureName') }}
          </p>
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.fee_category_id"
            :items="feeCategoryOptions"
            :label="required(t('finance.fields.fee_category_id'))"
            :hint="t('finance.hints.feeCategory')"
            persistent-hint
            :error-messages="form.errors.fee_category_id"
            required
          />
        </VCol>

        <VCol cols="12">
          <VAlert type="info" variant="tonal" density="compact">
            {{ t('finance.hints.scopeRule') }}
          </VAlert>
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.grade_level_id"
            :items="gradeLevelOptions"
            :label="t('finance.fields.grade_level_id')"
            :hint="t('finance.hints.gradeLevel')"
            persistent-hint
            clearable
            :error-messages="form.errors.grade_level_id"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.classroom_id"
            :items="classroomOptions"
            :label="t('finance.fields.classroom_id')"
            :hint="t('finance.hints.classroom')"
            persistent-hint
            clearable
            :error-messages="form.errors.classroom_id"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="amount">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-money" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('finance.sections.amount') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('finance.feeStructures.amountDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="4">
          <VTextField
            v-model="form.amount"
            type="number"
            step="0.01"
            min="0"
            :label="required(t('finance.fields.amount'))"
            :hint="t('finance.hints.amount')"
            persistent-hint
            :error-messages="form.errors.amount"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.frequency"
            :items="frequencyOptions"
            :label="required(t('finance.fields.frequency'))"
            :hint="t('finance.hints.frequency')"
            persistent-hint
            :error-messages="form.errors.frequency"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.due_day"
            type="number"
            min="1"
            max="31"
            :label="t('finance.fields.due_day')"
            :hint="t('finance.hints.dueDay')"
            persistent-hint
            :error-messages="form.errors.due_day"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSwitch
            v-model="form.is_optional"
            :label="t('finance.fields.is_optional')"
            color="primary"
            :error-messages="form.errors.is_optional"
          />
          <p class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('finance.hints.isOptional') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="late">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-time-five" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('finance.sections.lateFee') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('finance.feeStructures.lateFeeDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.late_fee_amount"
            type="number"
            step="0.01"
            min="0"
            :label="t('finance.fields.late_fee_amount')"
            :hint="t('finance.hints.lateFeeAmount')"
            persistent-hint
            :error-messages="form.errors.late_fee_amount"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.late_fee_days"
            type="number"
            min="0"
            :label="t('finance.fields.late_fee_days')"
            :hint="t('finance.hints.lateFeeDays')"
            persistent-hint
            :error-messages="form.errors.late_fee_days"
          />
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>
