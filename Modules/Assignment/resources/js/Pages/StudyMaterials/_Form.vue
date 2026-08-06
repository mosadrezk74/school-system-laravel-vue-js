<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

const props = defineProps({
  form: { type: Object, required: true },
  semesters: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const required = label => `${label} *`

const classOptions = computed(() => props.classes.map(c => ({
  title: [c.name, c.section_code].filter(Boolean).join(' · '),
  value: c.id,
})))

const semesterOptions = computed(() => props.semesters.map(s => ({
  title: s.name,
  value: s.id,
})))


const activeTab = ref('details')

const tabFields = {
  details: ['title', 'description', 'class_id', 'subject_id', 'teacher_id', 'semester_id', 'material_type'],
  publishing: ['is_published'],
}

const tabHasError = tab => tabFields[tab].some(field =>
  Object.keys(props.form.errors).some(key => key === field || key.startsWith(`${field}.`)))
</script>

<template>
  <VTabs
    v-model="activeTab"
    show-arrows
    class="v-tabs-pill"
  >
    <VTab value="details">
      <VIcon size="20" start icon="bx-detail" />
      {{ $t('studyMaterials.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="publishing">
      <VIcon size="20" start icon="bx-globe" />
      {{ $t('studyMaterials.sections.publishing') }}
      <VIcon v-if="tabHasError('publishing')" size="16" end icon="bx-error-circle" color="error" />
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
            {{ $t('studyMaterials.sections.details') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('studyMaterials.sections.detailsDesc') }}
          </p>
        </div>
      </div>

      <TranslatableTextInput
        v-model="form.title"
        field="title"
        :label="t('studyMaterials.titleField')"
        :errors="form.errors"
        required
        class="mb-4"
      />

      <TranslatableTextarea
        v-model="form.description"
        field="description"
        :label="t('studyMaterials.description')"
        :errors="form.errors"
        :rows="3"
        class="mb-4"
      />

      <VRow>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.semester_id"
            clearable
            :items="semesterOptions"
            :label="$t('studyMaterials.semester')"
            :hint="$t('studyMaterials.hints.semester')"
            persistent-hint
            :error-messages="form.errors.semester_id"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.class_id"
            clearable
            :items="classOptions"
            :label="$t('studyMaterials.class')"
            :hint="$t('studyMaterials.hints.class')"
            persistent-hint
            :error-messages="form.errors.class_id"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.subject_id"
            :items="subjects.map(s => ({ title: s.name, value: s.id }))"
            :label="required(t('studyMaterials.subject'))"
            :hint="$t('studyMaterials.hints.subject')"
            persistent-hint
            :error-messages="form.errors.subject_id"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.teacher_id"
            :items="teachers.map(u => ({ title: u.name, value: u.id }))"
            :label="required(t('studyMaterials.teacher'))"
            :hint="$t('studyMaterials.hints.teacher')"
            persistent-hint
            :error-messages="form.errors.teacher_id"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.material_type"
            :label="$t('studyMaterials.materialType')"
            :hint="$t('studyMaterials.hints.materialType')"
            persistent-hint
            :error-messages="form.errors.material_type"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="publishing">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-globe" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('studyMaterials.sections.publishing') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('studyMaterials.sections.publishingDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_published"
            :label="$t('studyMaterials.isPublished')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('studyMaterials.hints.isPublished') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>

<style scoped>
:deep(.v-input:not(.v-input--error) .v-messages) {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.75rem;
  opacity: var(--v-medium-emphasis-opacity);
}
</style>
