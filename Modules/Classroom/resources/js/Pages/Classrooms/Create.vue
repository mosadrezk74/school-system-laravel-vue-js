<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ClassroomForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  roomTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  capacity: 30,
  floor: null,
  building: '',
  room_type: 'classroom',
  has_projector: false,
  has_smartboard: false,
  is_active: true,
})

function submit() {
  form.post('/academic/classrooms')
}
</script>

<template>
  <Head :title="t('classroom.addNew')" />

  <AppPageHeader
    :title="t('classroom.addNew')"
    :subtitle="t('classroom.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <ClassroomForm
          :form="form"
          :room-types="roomTypes"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/academic/classrooms'"
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
