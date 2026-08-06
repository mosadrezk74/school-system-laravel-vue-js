<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  classes: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
})

const { t } = useI18n()

const required = label => `${label} *`

const form = useForm({
  class_id: null,
  academic_year_id: null,
  semester_id: null,
  is_active: true,
})

function submit() {
  form.post('/schedule/timetables')
}
</script>

<template>
  <Head :title="t('timetables.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('timetables.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('timetables.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <VRow>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.class_id"
              :items="classes.map(c => ({ title: `${c.name} ${c.section_code}`, value: c.id }))"
              :label="required(t('timetables.class'))"
              :error-messages="form.errors.class_id"
              required
            />
          </VCol>

          <VCol cols="12" md="6">
            <VSelect
              v-model="form.academic_year_id"
              :items="academicYears.map(y => ({ title: y.name, value: y.id }))"
              :label="required(t('timetables.academicYear'))"
              :error-messages="form.errors.academic_year_id"
              required
            />
          </VCol>
        </VRow>
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
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
    </VCardText>
  </VCard>
</template>
