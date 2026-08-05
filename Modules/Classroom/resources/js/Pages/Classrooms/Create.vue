<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ClassroomForm from './_Form.vue'

defineProps({
  branches: { type: Array, default: () => [] },
  roomTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  code: '',
  branch_id: null,
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('classroom.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('classroom.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <ClassroomForm
          :form="form"
          :branches="branches"
          :room-types="roomTypes"
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
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
