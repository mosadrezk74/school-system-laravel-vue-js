<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

const props = defineProps({
  form: { type: Object, required: true },
  subjects: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  types: { type: Array, default: () => [] },
  currentFileName: { type: String, default: null },
})

const { t } = useI18n()

const required = label => `${label} *`

const subjectOptions = computed(() => props.subjects.map(s => ({
  title: s.name,
  value: s.id,
})))

const gradeLevelOptions = computed(() => props.gradeLevels.map(g => ({
  title: g.name,
  value: g.id,
})))

const classroomOptions = computed(() => props.classrooms.map(c => ({
  title: c.name,
  value: c.id,
})))

const teacherOptions = computed(() => props.teachers.map(teacher => ({
  title: teacher.name,
  value: teacher.id,
})))

const typeOptions = computed(() => props.types.map(type => ({
  title: type.label,
  value: type.value,
})))

const activeTab = ref('details')

const tabFields = {
  details: ['title', 'description', 'subject_id', 'grade_level_id', 'classroom_id', 'teacher_id', 'type'],
  file: ['file', 'external_url'],
}

const tabHasError = tab => tabFields[tab].some(field =>
  Object.keys(props.form.errors).some(key => key === field || key.startsWith(`${field}.`)))

function onFileChange(value) {
  props.form.file = Array.isArray(value) ? (value[0] ?? null) : value
}
</script>

<template>
  <VTabs
    v-model="activeTab"
    show-arrows
    class="v-tabs-pill"
  >
    <VTab value="details">
      <VIcon size="20" start icon="bx-detail" />
      {{ t('studyResources.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="file">
      <VIcon size="20" start icon="bx-paperclip" />
      {{ t('studyResources.sections.file') }}
      <VIcon v-if="tabHasError('file')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="details">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-detail" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('studyResources.sections.details') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('studyResources.sections.detailsDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <TranslatableTextInput
            v-model="form.title"
            field="title"
            :label="t('studyResources.titleField')"
            :errors="form.errors"
            required
          />
          <p class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('studyResources.hints.title') }}
          </p>
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.type"
            :items="typeOptions"
            :label="required(t('studyResources.type'))"
            :hint="t('studyResources.hints.type')"
            persistent-hint
            :error-messages="form.errors.type"
            required
          />
        </VCol>

        <VCol cols="12">
          <TranslatableTextarea
            v-model="form.description"
            field="description"
            :label="t('studyResources.description')"
            :errors="form.errors"
          />
          <p class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('studyResources.hints.description') }}
          </p>
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.subject_id"
            :items="subjectOptions"
            :label="required(t('studyResources.subject'))"
            :hint="t('studyResources.hints.subject')"
            persistent-hint
            :error-messages="form.errors.subject_id"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.grade_level_id"
            :items="gradeLevelOptions"
            :label="required(t('studyResources.gradeLevel'))"
            :hint="t('studyResources.hints.gradeLevel')"
            persistent-hint
            :error-messages="form.errors.grade_level_id"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.classroom_id"
            clearable
            :items="classroomOptions"
            :label="t('studyResources.classroom')"
            :hint="t('studyResources.hints.classroom')"
            persistent-hint
            :error-messages="form.errors.classroom_id"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.teacher_id"
            :items="teacherOptions"
            :label="required(t('studyResources.teacher'))"
            :hint="t('studyResources.hints.teacher')"
            persistent-hint
            :error-messages="form.errors.teacher_id"
            required
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="file">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-paperclip" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('studyResources.sections.file') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('studyResources.sections.fileDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VFileInput
            :label="t('studyResources.file')"
            :hint="t('studyResources.hints.file')"
            persistent-hint
            prepend-icon=""
            prepend-inner-icon="bx-paperclip"
            :error-messages="form.errors.file"
            @update:model-value="onFileChange"
          />
          <p v-if="currentFileName" class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('studyResources.currentFile') }}: {{ currentFileName }}
          </p>
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.external_url"
            :label="t('studyResources.externalUrl')"
            :hint="t('studyResources.hints.externalUrl')"
            persistent-hint
            :error-messages="form.errors.external_url"
          />
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>
