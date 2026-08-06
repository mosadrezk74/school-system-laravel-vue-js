<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PeriodForm from './_Form.vue'

const { t } = useI18n()

const form = useForm({
  period_number: null,
  name: {},
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('timetables.periods.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('timetables.periods.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <PeriodForm :form="form" />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
