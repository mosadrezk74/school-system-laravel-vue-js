<script setup>
import { useI18n } from 'vue-i18n'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

defineProps({
  form: { type: Object, required: true },
  teachers: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
})

const { t } = useI18n()

const required = label => `${label} *`

</script>

<template>
  <div class="d-flex align-center gap-3 mb-6">
    <VAvatar color="primary" variant="tonal" size="40">
      <VIcon icon="bx-video" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('meetings.sections.details') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('meetings.sections.detailsDesc') }}
      </p>
    </div>
  </div>

  <VRow>
    <VCol cols="12">
      <VTextField
        v-model="form.title"
        maxlength="255"
        :counter="255"
        :label="required(t('meetings.titleField'))"
        :hint="t('meetings.hints.title')"
        persistent-hint
        :error-messages="form.errors.title"
        required
        class="mb-4"
      />
    </VCol>

    <VCol cols="12">
      <TranslatableTextarea
        v-model="form.description"
        :maxlength="2000"
        field="description"
        :label="t('meetings.description')"
        :errors="form.errors"
        :rows="3"
      />
    </VCol>



    <VCol cols="12" md="4">
      <VSelect
        v-model="form.semester_id"
        clearable
        :items="semesters.map(s => ({ title: s.name, value: s.id }))"
        :label="t('meetings.semester')"
        :hint="t('meetings.hints.semester')"
        persistent-hint
        :error-messages="form.errors.semester_id"
      />
    </VCol>

    <VCol cols="12" md="4">
      <VTextField
        v-model="form.duration_minutes"
        inputmode="numeric"
        max="600"
        step="1"
        type="number"
        min="1"
        :label="required(t('meetings.durationMinutes'))"
        :hint="t('meetings.hints.durationMinutes')"
        persistent-hint
        :error-messages="form.errors.duration_minutes"
        required
      />
    </VCol>

    <VCol cols="12" md="4">
      <VTextField
        v-model="form.scheduled_at"
        type="datetime-local"
        :label="required(t('meetings.scheduledAt'))"
        :hint="t('meetings.hints.scheduledAt')"
        persistent-hint
        :error-messages="form.errors.scheduled_at"
        required
      />
    </VCol>
  </VRow>
</template>
