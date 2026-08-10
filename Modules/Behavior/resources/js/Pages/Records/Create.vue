<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BehaviorRecordForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  students: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  studentId: { type: Number, default: null },
})

const { t } = useI18n()

const form = useForm({
  student_id: props.studentId,
  behavior_category_id: null,
  date: new Date().toISOString().slice(0, 10),
  description: '',
})

function submit() {
  form.post('/behavior/records')
}
</script>

<template>
  <Head :title="t('behaviorRecords.addNew')" />

  <AppPageHeader
    :title="t('behaviorRecords.addNew')"
    :subtitle="t('behaviorRecords.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <BehaviorRecordForm
        :form="form"
        :students="students"
        :categories="categories"
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
