<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ClassroomForm from './_Form.vue'

const props = defineProps({
  classroom: { type: Object, required: true },
  roomTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: props.classroom.name || '',
  code: props.classroom.code || '',
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('classroom.edit') }}</VCardTitle>
      <VCardSubtitle>{{ classroom.name }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <ClassroomForm
          :form="form"
          :room-types="roomTypes"
          is-edit
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
