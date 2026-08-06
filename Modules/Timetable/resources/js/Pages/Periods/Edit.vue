<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PeriodForm from './_Form.vue'

const props = defineProps({
  period: { type: Object, required: true },
})

const { t } = useI18n()

const form = useForm({
  period_number: props.period.period_number,
  name: { ...props.period.name_translations },
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('timetables.periods.edit') }}</VCardTitle>
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
