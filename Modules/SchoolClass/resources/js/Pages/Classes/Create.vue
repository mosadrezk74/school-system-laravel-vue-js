<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ClassForm from './_Form.vue'

defineProps({
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  grade_level_id: null,
  classroom_id: null,
  name: '',
  capacity: 30,
  is_active: true,
})

function submit() {
  form.post('/academic/classes')
}
</script>

<template>
  <Head :title="t('classes.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('classes.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('classes.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <ClassForm
          :form="form"
          :grade-levels="gradeLevels"
          :classrooms="classrooms"
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/academic/classes'"
        :as="Link"
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
