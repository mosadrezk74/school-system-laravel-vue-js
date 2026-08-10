<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ClassroomForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  classroom: { type: Object, required: true },
  roomTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: props.classroom.name || '',
  capacity: props.classroom.capacity || 30,
  floor: props.classroom.floor || null,
  building: props.classroom.building || '',
  room_type: props.classroom.room_type || 'classroom',
  has_projector: props.classroom.has_projector || false,
  has_smartboard: props.classroom.has_smartboard || false,
  is_active: props.classroom.is_active !== undefined ? props.classroom.is_active : true,
})

function submit() {
  form.put(`/academic/classrooms/${props.classroom.id}`)
}
</script>

<template>
  <Head :title="t('classroom.edit')" />

  <AppPageHeader
    :title="t('classroom.edit')"
    :subtitle="classroom.name"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <ClassroomForm
          :form="form"
          :room-types="roomTypes"
          is-edit
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
        {{ t('common.update') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
