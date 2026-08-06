<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

const props = defineProps({
  form: { type: Object, required: true },
  subjectTypes: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

const { t } = useI18n()

const activeTab = ref('basic')

const tabFields = {
  basic: ['name', 'code', 'short_name', 'subject_type'],
  details: ['description'],
  grading: ['pass_grade', 'credit_hours', 'max_marks'],
  appearance: ['color', 'icon', 'is_active'],
}

const tabHasError = tab => tabFields[tab].some(field =>
  Object.keys(props.form.errors).some(key => key === field || key.startsWith(`${field}.`)))


const colorOptions = [
  { title: 'Blue', value: '#2563eb' },
  { title: 'Red', value: '#dc2626' },
  { title: 'Green', value: '#16a34a' },
  { title: 'Yellow', value: '#ca8a04' },
  { title: 'Purple', value: '#9333ea' },
  { title: 'Pink', value: '#db2777' },
  { title: 'Orange', value: '#ea580c' },
  { title: 'Cyan', value: '#0891b2' },
]
</script>

<template>
  <VTabs
    v-model="activeTab"
    show-arrows
    class="v-tabs-pill"
  >
    <VTab value="basic">
      <VIcon size="20" start icon="bx-info-circle" />
      {{ $t('subject.sections.basic') }}
      <VIcon v-if="tabHasError('basic')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="details">
      <VIcon size="20" start icon="bx-file" />
      {{ $t('subject.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="grading">
      <VIcon size="20" start icon="bx-bar-chart" />
      {{ $t('subject.sections.grading') }}
      <VIcon v-if="tabHasError('grading')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="appearance">
      <VIcon size="20" start icon="bx-palette" />
      {{ $t('subject.sections.appearance') }}
      <VIcon v-if="tabHasError('appearance')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="basic">
      <TranslatableTextInput
        v-model="form.name"
        field="name"
        :label="t('subject.fields.name')"
        :errors="form.errors"
        required
        class="mb-4"
      />

      <VRow>
        <VCol cols="12" md="4">
          <VTextField
            v-model="form.code"
            :label="$t('subject.fields.code')"
            :hint="$t('subject.hints.code')"
            persistent-hint
            :error-messages="form.errors.code"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.short_name"
            :label="$t('subject.fields.short_name')"
            :hint="$t('subject.hints.short_name')"
            persistent-hint
            :error-messages="form.errors.short_name"
          />
        </VCol>


        <VCol cols="12" md="6">
          <VSelect
            v-model="form.subject_type"
            :items="subjectTypes"
            :label="$t('subject.fields.subject_type')"
            :hint="$t('subject.hints.subject_type')"
            persistent-hint
            :error-messages="form.errors.subject_type"
            required
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="details">
      <TranslatableTextarea
        v-model="form.description"
        field="description"
        :label="t('subject.fields.description')"
        :errors="form.errors"
      />
    </VWindowItem>

    <VWindowItem value="grading">
      <VRow>
        <VCol cols="12" md="4">
          <VTextField
            v-model="form.pass_grade"
            type="number"
            min="0"
            max="100"
            step="0.5"
            :label="$t('subject.fields.pass_grade')"
            :hint="$t('subject.hints.pass_grade')"
            persistent-hint
            :error-messages="form.errors.pass_grade"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.credit_hours"
            type="number"
            min="0"
            max="10"
            step="0.5"
            :label="$t('subject.fields.credit_hours')"
            :hint="$t('subject.hints.credit_hours')"
            persistent-hint
            :error-messages="form.errors.credit_hours"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.max_marks"
            type="number"
            min="0"
            max="1000"
            step="0.5"
            :label="$t('subject.fields.max_marks')"
            :hint="$t('subject.hints.max_marks')"
            persistent-hint
            :error-messages="form.errors.max_marks"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="appearance">
      <VRow>
        <VCol cols="12" md="6">
          <VSelect
            v-model="form.color"
            :items="colorOptions"
            :label="$t('subject.fields.color')"
            :hint="$t('subject.hints.color')"
            persistent-hint
            :error-messages="form.errors.color"
          >
            <template #item="{ item, props }">
              <VListItem v-bind="props">
                <template #prepend>
                  <div
                    :style="{
                      width: '24px',
                      height: '24px',
                      borderRadius: '4px',
                      backgroundColor: item.value,
                      marginRight: '12px'
                    }"
                  />
                </template>
                {{ item.title }}
              </VListItem>
            </template>
            <template #selection="{ item }">
              <div class="d-flex align-center">
                <div
                  :style="{
                    width: '20px',
                    height: '20px',
                    borderRadius: '4px',
                    backgroundColor: item.value,
                    marginRight: '8px'
                  }"
                />
                {{ item.title }}
              </div>
            </template>
          </VSelect>
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.icon"
            :label="$t('subject.fields.icon')"
            :hint="$t('subject.hints.icon')"
            persistent-hint
            :error-messages="form.errors.icon"
            placeholder="bx-book"
          />
        </VCol>

        <VCol cols="12" md="6" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_active"
            :label="$t('subject.fields.is_active')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('subject.hints.is_active') }}
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
