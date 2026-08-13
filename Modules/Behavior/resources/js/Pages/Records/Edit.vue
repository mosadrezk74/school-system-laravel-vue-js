<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BehaviorRecordForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  /** Sent by the controller from the same enum the rules validate against. */
  recordStatuses: { type: Array, default: () => [] },
  record: { type: Object, required: true },
  students: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  student_id: props.record.student_id,
  behavior_category_id: props.record.behavior_category_id,
  date: props.record.date,
  description: props.record.description ?? '',
  status: props.record.status,
  reviewed_by: props.record.reviewed_by,
})

function submit() {
  form.put(`/behavior/records/${props.record.id}`)
}
</script>

<template>
  <Head :title="t('behaviorRecords.edit')" />

  <AppPageHeader
    :title="t('behaviorRecords.edit')"
    :subtitle="record.student?.name"
  />

  <VCard class="app-form-card">

    <VCardText>
      <BehaviorRecordForm
        :form="form"
        :students="students"
        :categories="categories"
        :record-statuses="recordStatuses"
        is-edit
      />
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/behavior/records'"
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
