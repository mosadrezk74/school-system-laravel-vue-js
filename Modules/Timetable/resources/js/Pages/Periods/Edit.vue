<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PeriodForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  period: { type: Object, required: true },
})

const { t } = useI18n()

const form = useForm({
  period_number: props.period.period_number,
  name: props.period.name || '',
  start_time: props.period.start_time,
  end_time: props.period.end_time,
  is_break: props.period.is_break,
  is_active: props.period.is_active,
})

function submit() {
  form.put(`/schedule/periods/${props.period.id}`)
}
</script>

<template>
  <Head :title="t('timetables.periods.edit')" />

  <AppPageHeader
    :title="t('timetables.periods.edit')"
    :subtitle="t('timetables.periods.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <PeriodForm :form="form" />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/schedule/periods'"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        :loading="form.processing"
        @click="submit"
      >
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
