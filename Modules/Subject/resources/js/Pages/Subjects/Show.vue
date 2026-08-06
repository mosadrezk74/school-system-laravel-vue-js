<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  subject: { type: Object, required: true },
})

const { t } = useI18n()
const page = usePage()

const languages = computed(() => page.props.languages ?? [])

const nameTranslations = computed(() => languages.value
  .map(language => ({ language, value: props.subject.name_translations?.[language.locale] }))
  .filter(entry => entry.value))

const descriptionTranslations = computed(() => languages.value
  .map(language => ({ language, value: props.subject.description_translations?.[language.locale] }))
  .filter(entry => entry.value))

const typeLabels = {
  core: t('subject.types.core'),
  elective: t('subject.types.elective'),
  extracurricular: t('subject.types.extracurricular'),
}

const basicInfo = [
  { key: 'code', label: 'subject.fields.code', value: props.subject.code || '—' },
  { key: 'short_name', label: 'subject.fields.short_name', value: props.subject.short_name || '—' },
  { key: 'branch', label: 'subject.fields.branch_id', value: props.subject.branch?.name || '—' },
  { key: 'subject_type', label: 'subject.fields.subject_type', value: typeLabels[props.subject.subject_type] || props.subject.subject_type || '—' },
]

const gradingInfo = [
  { key: 'pass_grade', label: 'subject.fields.pass_grade', value: props.subject.pass_grade || '—' },
  { key: 'credit_hours', label: 'subject.fields.credit_hours', value: props.subject.credit_hours || '—' },
  { key: 'max_marks', label: 'subject.fields.max_marks', value: props.subject.max_marks || '—' },
]

const appearanceInfo = [
  { key: 'color', label: 'subject.fields.color', value: props.subject.color || '—' },
  { key: 'icon', label: 'subject.fields.icon', value: props.subject.icon || '—' },
]
</script>

<template>
  <Head :title="subject.name || subject.code" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <div
              :style="{
                width: '40px',
                height: '40px',
                borderRadius: '8px',
                backgroundColor: subject.color || '#2563eb',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: '#fff'
              }"
            >
              <VIcon :icon="subject.icon || 'bx-book'" size="24" />
            </div>
          </template>
          <VCardTitle>{{ subject.name || subject.code }}</VCardTitle>
          <VCardSubtitle v-if="nameTranslations.length" class="d-flex flex-wrap gap-2 mt-1">
            <VChip v-for="entry in nameTranslations" :key="entry.language.locale" size="x-small" variant="tonal">
              {{ entry.language.name }}: {{ entry.value }}
            </VChip>
          </VCardSubtitle>
          <template #append>
            <VChip :color="subject.is_active ? 'success' : 'secondary'" size="small">
              {{ subject.is_active ? t('common.active') : t('common.inactive') }}
            </VChip>
            <IconBtn :href="`/academic/subjects/${subject.id}/edit`" :as="Link" class="ms-2">
              <VIcon icon="bx-edit" />
            </IconBtn>
          </template>
        </VCardItem>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-info-circle" />
          </template>
          <VCardTitle>{{ t('subject.sections.basic') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow>
            <VCol v-for="info in basicInfo" :key="info.key" :cols="12" :md="6">
              <strong>{{ t(info.label) }}:</strong> {{ info.value }}
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-file" />
          </template>
          <VCardTitle>{{ t('subject.sections.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <div v-for="entry in descriptionTranslations" :key="entry.language.locale" class="mb-3">
            <strong>{{ t('subject.fields.description') }} ({{ entry.language.name }}):</strong>
            <p>{{ entry.value }}</p>
          </div>
          <div v-if="!descriptionTranslations.length" class="text-disabled">
            {{ t('common.noItems') }}
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-bar-chart" />
          </template>
          <VCardTitle>{{ t('subject.sections.grading') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow>
            <VCol v-for="info in gradingInfo" :key="info.key" :cols="12" :md="4">
              <strong>{{ t(info.label) }}:</strong> {{ info.value }}
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-palette" />
          </template>
          <VCardTitle>{{ t('subject.sections.appearance') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow>
            <VCol v-for="info in appearanceInfo" :key="info.key" :cols="12" :md="6">
              <strong>{{ t(info.label) }}:</strong>
              <span v-if="info.key === 'color'">
                <div
                  :style="{
                    display: 'inline-block',
                    width: '20px',
                    height: '20px',
                    borderRadius: '4px',
                    backgroundColor: info.value,
                    marginLeft: '8px',
                    verticalAlign: 'middle'
                  }"
                />
              </span>
              <span v-else>{{ info.value }}</span>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
