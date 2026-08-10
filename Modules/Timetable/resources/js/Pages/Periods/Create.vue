<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PeriodForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const { t } = useI18n()

const form = useForm({
  period_number: null,
  name: '',
  start_time: null,
  end_time: null,
  is_break: false,
  is_active: true,
})

function submit() {
  form.post('/schedule/periods')
}
</script>

<template>
  <Head :title="t('timetables.periods.addNew')" />

  <AppPageHeader
    :title="t('timetables.periods.addNew')"
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
