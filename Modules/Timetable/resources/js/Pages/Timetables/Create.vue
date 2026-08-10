<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  classes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const required = label => `${label} *`

const form = useForm({
  class_id: null,
  semester_id: null,
  is_active: true,
})

function submit() {
  form.post('/schedule/timetables')
}
</script>

<template>
  <Head :title="t('timetables.addNew')" />

  <AppPageHeader
    :title="t('timetables.addNew')"
    :subtitle="t('timetables.subtitle')"
  />

  <VCard class="app-form-card">

    <VCardText>
      <form @submit.prevent="submit">
        <VRow>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.class_id"
              :items="classes.map(c => ({ title: c.name, value: c.id }))"
              :label="required(t('timetables.class'))"
              :error-messages="form.errors.class_id"
              required
            />
          </VCol>
        </VRow>
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/schedule/timetables'"
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
