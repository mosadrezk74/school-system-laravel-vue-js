<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

defineOptions({ layout: TeacherLayout })

const props = defineProps({
  meeting: { type: Object, default: null },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
})

const { t } = useI18n()

const isEdit = computed(() => props.meeting !== null)

const classOptions = computed(() => props.classes.map(c => ({ title: c.name, value: c.id })))
const subjectOptions = computed(() => props.subjects.map(s => ({ title: s.name, value: s.id })))

const form = useForm({
  title: props.meeting?.title ?? '',
  description: props.meeting?.description ?? '',
  school_class_id: props.meeting?.school_class_id ?? null,
  subject_id: props.meeting?.subject_id ?? null,
  scheduled_at: props.meeting?.scheduled_at?.slice(0, 16) ?? '',
  duration_minutes: props.meeting?.duration_minutes ?? 45,
})

function submit() {
  if (isEdit.value)
    form.put(`/teacher/meetings/${props.meeting.id}`)
  else
    form.post('/teacher/meetings')
}
</script>

<template>
  <Head :title="isEdit ? t('portal.teacher.editMeeting') : t('portal.teacher.newMeeting')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="isEdit ? t('portal.teacher.editMeeting') : t('portal.teacher.newMeeting')"
        :subtitle="t('portal.teacher.meetingsSubtitle')"
        icon="bx-video"
      />
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <form @submit.prevent="submit">
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.title"
                  maxlength="255"
                  :counter="255"
                  :label="t('portal.teacher.meetingTitle')"
                  :error-messages="form.errors.title"
                />
              </VCol>

              <VCol cols="12" md="3">
                <VSelect
                  v-model="form.school_class_id"
                  :items="classOptions"
                  :label="t('portal.teacher.selectClass')"
                  clearable
                  :error-messages="form.errors.school_class_id"
                />
              </VCol>

              <VCol cols="12" md="3">
                <VSelect
                  v-model="form.subject_id"
                  :items="subjectOptions"
                  :label="t('portal.teacher.subject')"
                  clearable
                  :error-messages="form.errors.subject_id"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.scheduled_at"
                  type="datetime-local"
                  :label="t('portal.teacher.scheduledAt')"
                  :error-messages="form.errors.scheduled_at"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.duration_minutes"
                  inputmode="numeric"
                  max="600"
                  step="1"
                  type="number"
                  min="5"
                  :label="t('portal.teacher.duration')"
                  :error-messages="form.errors.duration_minutes"
                />
              </VCol>

              <VCol cols="12">
                <TranslatableTextarea
                  v-model="form.description"
                  :maxlength="2000"
                  field="description"
                  :label="t('portal.teacher.description')"
                  :errors="form.errors"
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
            href="/teacher/meetings"
            :as="Link"
          >
            {{ t('common.cancel') }}
          </VBtn>
          <VBtn :loading="form.processing" @click="submit">
            {{ isEdit ? t('common.update') : t('common.save') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
