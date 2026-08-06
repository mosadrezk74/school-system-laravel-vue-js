<script setup>
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'

defineProps({
  form: { type: Object, required: true },
})

const { t } = useI18n()

const required = label => `${label} *`
</script>

<template>
  <div class="d-flex align-center gap-3 mb-6">
    <VAvatar color="primary" variant="tonal" size="40">
      <VIcon icon="bx-time-five" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('timetables.periods.sections.details') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('timetables.periods.sections.detailsDesc') }}
      </p>
    </div>
  </div>

  <VRow>
    <VCol cols="12" md="4">
      <VTextField
        v-model="form.period_number"
        type="number"
        min="1"
        :label="required(t('timetables.periods.periodNumber'))"
        :hint="t('timetables.periods.hints.periodNumber')"
        persistent-hint
        :error-messages="form.errors.period_number"
        required
      />
    </VCol>

    <VCol cols="12" md="4">
      <VTextField
        v-model="form.start_time"
        type="time"
        :label="required(t('timetables.periods.startTime'))"
        :error-messages="form.errors.start_time"
        required
      />
    </VCol>

    <VCol cols="12" md="4">
      <VTextField
        v-model="form.end_time"
        type="time"
        :label="required(t('timetables.periods.endTime'))"
        :error-messages="form.errors.end_time"
        required
      />
    </VCol>
  </VRow>

  <TranslatableTextInput
    v-model="form.name"
    field="name"
    :label="t('timetables.periods.name')"
    :errors="form.errors"
    required
    class="mt-2"
  />

  <VRow class="mt-2">
    <VCol cols="12" md="6" class="d-flex flex-column justify-center gap-2">
      <VSwitch
        v-model="form.is_break"
        :label="t('timetables.periods.isBreak')"
      />
      <VSwitch
        v-model="form.is_active"
        :label="t('timetables.periods.isActive')"
      />
    </VCol>
  </VRow>
</template>
