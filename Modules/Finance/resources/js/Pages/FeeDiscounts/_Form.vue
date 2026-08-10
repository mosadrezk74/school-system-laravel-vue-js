<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  form: { type: Object, required: true },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  discountTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const required = label => `${label} *`

const gradeLevelOptions = computed(() => props.gradeLevels.map(g => ({ title: g.name, value: g.id })))
const classroomOptions = computed(() => props.classrooms.map(c => ({ title: c.name, value: c.id })))
const discountTypeOptions = computed(() => props.discountTypes.map(d => ({ title: d.label, value: d.value })))

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
  details: ['name', 'code', 'discount_type', 'discount_value', 'max_amount'],
  scope: ['grade_level_id', 'classroom_id'],
  validity: ['start_date', 'end_date', 'usage_limit', 'is_active'],
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
      <VIcon size="20" start icon="bx-gift" />
      {{ t('finance.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="scope">
      <VIcon size="20" start icon="bx-target-lock" />
      {{ t('finance.sections.scope') }}
      <VIcon v-if="tabHasError('scope')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="validity">
      <VIcon size="20" start icon="bx-calendar" />
      {{ t('finance.sections.validity') }}
      <VIcon v-if="tabHasError('validity')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="details">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-gift" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('finance.sections.details') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('finance.feeDiscounts.sectionDesc') }}
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
            {{ t('finance.hints.discountName') }}
          </p>
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.code"
            :label="t('finance.fields.code')"
            :hint="t('finance.hints.code')"
            persistent-hint
            :error-messages="form.errors.code"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.discount_type"
            :items="discountTypeOptions"
            :label="required(t('finance.fields.discount_type'))"
            :hint="t('finance.hints.discountType')"
            persistent-hint
            :error-messages="form.errors.discount_type"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.discount_value"
            type="number"
            step="0.01"
            min="0"
            :label="required(t('finance.fields.discount_value'))"
            :hint="t('finance.hints.discountValue')"
            persistent-hint
            :error-messages="form.errors.discount_value"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.max_amount"
            type="number"
            step="0.01"
            min="0"
            :label="t('finance.fields.max_amount')"
            :hint="t('finance.hints.maxAmount')"
            persistent-hint
            :error-messages="form.errors.max_amount"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="scope">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-target-lock" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('finance.sections.scope') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('finance.feeDiscounts.scopeDesc') }}
          </p>
        </div>
      </div>

      <VRow>
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

    <VWindowItem value="validity">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-calendar" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('finance.sections.validity') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('finance.feeDiscounts.validityDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="4">
          <AppDateField
            v-model="form.start_date"
            :label="t('finance.fields.start_date')"
            :hint="t('finance.hints.startDate')"
            persistent-hint
            :error-messages="form.errors.start_date"
          />
        </VCol>

        <VCol cols="12" md="4">
          <AppDateField
            v-model="form.end_date"
            :label="t('finance.fields.end_date')"
            :hint="t('finance.hints.endDate')"
            persistent-hint
            :error-messages="form.errors.end_date"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.usage_limit"
            type="number"
            min="1"
            :label="t('finance.fields.usage_limit')"
            :hint="t('finance.hints.usageLimit')"
            persistent-hint
            :error-messages="form.errors.usage_limit"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSwitch
            v-model="form.is_active"
            :label="t('finance.fields.is_active')"
            color="primary"
            :error-messages="form.errors.is_active"
          />
          <p class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('finance.hints.isActive') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>
