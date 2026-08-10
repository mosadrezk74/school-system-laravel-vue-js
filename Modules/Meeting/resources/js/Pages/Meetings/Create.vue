<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import MeetingForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  teachers: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  title: '',
  description: '',
  password: '',
  scheduled_at: null,
  duration_minutes: 45,
  teacher_id: null,
  semester_id: null,
})

function submit() {
  form.post('/meetings/meetings')
}
</script>

<template>
  <Head :title="t('meetings.addNew')" />

  <AppPageHeader
    :title="t('meetings.addNew')"
    :subtitle="t('meetings.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <MeetingForm
          :form="form"
          :teachers="teachers"
          :semesters="semesters"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/meetings/meetings'"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        type="submit"
        :loading="form.processing"
        color="primary"
        @click="submit"
      >
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
