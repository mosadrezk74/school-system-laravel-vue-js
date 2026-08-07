<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

defineOptions({ layout: TeacherLayout })

const props = defineProps({
  studyResource: { type: Object, default: null },
  subjects: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  types: { type: Array, default: () => [] },
})

const { t } = useI18n()

const isEdit = computed(() => props.studyResource !== null)

const subjectOptions = computed(() => props.subjects.map(s => ({ title: s.name, value: s.id })))
const gradeOptions = computed(() => props.gradeLevels.map(g => ({ title: g.name, value: g.id })))
const typeOptions = computed(() => props.types.map(item => ({ title: item.label, value: item.value })))

const form = useForm({
  _method: isEdit.value ? 'put' : null,
  title: props.studyResource?.title ?? '',
  description: props.studyResource?.description ?? '',
  subject_id: props.studyResource?.subject_id ?? null,
  grade_level_id: props.studyResource?.grade_level_id ?? null,
  classroom_id: props.studyResource?.classroom_id ?? null,
  type: props.studyResource?.type ?? null,
  external_url: props.studyResource?.external_url ?? '',
  file: null,
})

function onFileChange(value) {
  form.file = Array.isArray(value) ? (value[0] ?? null) : value
}

function submit() {
  if (isEdit.value)
    form.post(`/teacher/study-materials/${props.studyResource.id}`, { forceFormData: true })
  else
    form.post('/teacher/study-materials', { forceFormData: true })
}
</script>

<template>
  <Head :title="isEdit ? t('portal.teacher.editMaterial') : t('portal.teacher.uploadMaterial')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="isEdit ? t('portal.teacher.editMaterial') : t('portal.teacher.uploadMaterial')"
        :subtitle="t('portal.teacher.materialsSubtitle')"
        icon="bx-folder-open"
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
                  field="title"
                  :label="t('portal.teacher.materialTitle')"
                  :errors="form.errors"
                  required
                />
              </VCol>

              <VCol cols="12" md="3">
                <VSelect
                  v-model="form.type"
                  :items="typeOptions"
                  :label="t('portal.teacher.materialType')"
                  :error-messages="form.errors.type"
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

              <VCol cols="12" md="4">
                <VSelect
                  v-model="form.grade_level_id"
                  :items="gradeOptions"
                  :label="t('portal.teacher.gradeLevel')"
                  :error-messages="form.errors.grade_level_id"
                />
              </VCol>

              <VCol cols="12">
                <TranslatableTextarea
                  v-model="form.description"
                  field="description"
                  :label="t('portal.teacher.description')"
                  :errors="form.errors"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VFileInput
                  :label="t('portal.teacher.file')"
                  prepend-icon=""
                  prepend-inner-icon="bx-paperclip"
                  :error-messages="form.errors.file"
                  @update:model-value="onFileChange"
                />
                <p v-if="studyResource?.file_name" class="text-caption text-medium-emphasis mb-0 mt-1">
                  {{ t('portal.teacher.currentFile') }}: {{ studyResource.file_name }}
                </p>
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.external_url"
                  :label="t('portal.teacher.externalUrl')"
                  :error-messages="form.errors.external_url"
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
            href="/teacher/study-materials"
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
