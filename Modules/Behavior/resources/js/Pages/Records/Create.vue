<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BehaviorRecordForm from './_Form.vue'

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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('behaviorRecords.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('behaviorRecords.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <BehaviorRecordForm
        :form="form"
        :students="students"
        :categories="categories"
      />
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
