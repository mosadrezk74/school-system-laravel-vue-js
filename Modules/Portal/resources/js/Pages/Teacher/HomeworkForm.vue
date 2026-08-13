<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'
import AppDateField from '@/components/AppDateField.vue'

defineOptions({ layout: TeacherLayout })

const props = defineProps({
  assignment: { type: Object, default: null },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
})

const { t } = useI18n()

const isEdit = computed(() => props.assignment !== null)

const classOptions = computed(() => props.classes.map(c => ({ title: c.name, value: c.id })))
const subjectOptions = computed(() => props.subjects.map(s => ({ title: s.name, value: s.id })))

const form = useForm({
  class_id: props.assignment?.class_id ?? null,
  subject_id: props.assignment?.subject_id ?? null,
  title: props.assignment?.title ?? '',
  description: props.assignment?.description ?? '',
  max_score: props.assignment?.max_score ?? 10,
  deadline: props.assignment?.deadline?.slice(0, 10) ?? '',
  is_published: props.assignment ? Boolean(props.assignment.published_at) : false,
})

function submit() {
  if (isEdit.value)
    form.put(`/teacher/homework/${props.assignment.id}`)
  else
    form.post('/teacher/homework')
}
</script>

<template>
  <Head :title="isEdit ? t('portal.teacher.editHomework') : t('portal.teacher.newHomework')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="isEdit ? t('portal.teacher.editHomework') : t('portal.teacher.newHomework')"
        :subtitle="t('portal.teacher.homeworkSubtitle')"
        icon="bx-task"
      />
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <form @submit.prevent="submit">
            <VRow>
              <VCol cols="12" md="6">
                <TranslatableTextInput
                  v-model="form.title"
                  :maxlength="150"
                  field="title"
                  :label="t('portal.teacher.homeworkTitle')"
                  :errors="form.errors"
                  required
                />
              </VCol>

              <VCol cols="12" md="3">
                <VSelect
                  v-model="form.class_id"
                  :items="classOptions"
                  :label="t('portal.teacher.selectClass')"
                  :error-messages="form.errors.class_id"
                />
              </VCol>

              <VCol cols="12" md="3">
                <VSelect
                  v-model="form.subject_id"
                  :items="subjectOptions"
                  :label="t('portal.teacher.subject')"
                  :error-messages="form.errors.subject_id"
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

              <VCol cols="12" md="4">
                <AppDateField
                  v-model="form.deadline"
                  :label="t('portal.teacher.deadline')"
                  :error-messages="form.errors.deadline"
                />
              </VCol>

              <VCol cols="12" md="4">
                <VTextField
                  v-model="form.max_score"
                  inputmode="decimal"
                  max="9999.99"
                  type="number"
                  min="0"
                  step="0.5"
                  :label="t('portal.teacher.maxScore')"
                  :error-messages="form.errors.max_score"
                />
              </VCol>

              <VCol cols="12" md="4">
                <VSwitch
                  v-model="form.is_published"
                  color="primary"
                  :label="t('portal.teacher.publishNow')"
                  :error-messages="form.errors.is_published"
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
            href="/teacher/homework"
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
