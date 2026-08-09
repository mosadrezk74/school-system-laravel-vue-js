<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ClassForm from './_Form.vue'

const props = defineProps({
  class: {
    type: Object,
    required: true,
  },
  gradeLevels: {
    type: Array,
    default: () => [],
  },
  classrooms: {
    type: Array,
    default: () => [],
  },
})

const { t } = useI18n()

const form = useForm({
  grade_level_id: props.class.grade_level_id,
  classroom_id: props.class.classroom_id,
  name: props.class.name,
  capacity: props.class.capacity,
  is_active: props.class.is_active,
})

function submit() {
  form.put(`/academic/classes/${props.class.id}`)
}
</script>

<template>
  <Head :title="t('classes.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>
        {{ t('classes.edit') }}
      </VCardTitle>

      <VCardSubtitle>
        {{ $props.class.name }}
      </VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <ClassForm
          :form="form"
          :grade-levels="gradeLevels"
          :classrooms="classrooms"
          is-edit
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        :as="Link"
        href="/academic/classes"
        color="secondary"
        variant="tonal"
      >
        {{ t('common.cancel') }}
      </VBtn>

      <VBtn
        :loading="form.processing"
        @click="submit"
      >
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>