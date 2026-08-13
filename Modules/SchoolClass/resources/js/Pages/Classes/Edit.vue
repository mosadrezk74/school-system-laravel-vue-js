<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ClassForm from './_Form.vue'

const props = defineProps({
  /*
   * Named `schoolClass`, not `class`: Vue reserves `class` as an attribute
   * and normalizeClass() would flatten the object into a class string.
   */
  schoolClass: {
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
  grade_level_id: props.schoolClass.grade_level_id,
  classroom_id: props.schoolClass.classroom_id,
  name: props.schoolClass.name,
  capacity: props.schoolClass.capacity,
  is_active: props.schoolClass.is_active,
})

function submit() {
  form.put(`/academic/classes/${props.schoolClass.id}`)
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
        {{ $props.schoolClass.name }}
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