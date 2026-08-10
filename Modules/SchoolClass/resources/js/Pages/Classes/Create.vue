<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ClassForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

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

  <AppPageHeader
    :title="t('classes.addNew')"
    :subtitle="t('classes.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <ClassForm
          :form="form"
          :grade-levels="gradeLevels"
          :classrooms="classrooms"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
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
    </VCardActions>
  </VCard>
</template>
