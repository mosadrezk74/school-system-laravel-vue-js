<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  subject: { type: Object, required: true },
})

const { t } = useI18n()

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
  <Head :title="subject.name_en || subject.name?.en || subject.code" />

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
          <VCardTitle>{{ subject.name_en || subject.name?.en || subject.code }}</VCardTitle>
          <VCardSubtitle v-if="subject.name_ar || subject.name?.ar">
            {{ subject.name_ar || subject.name?.ar }}
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
          <div v-if="subject.description_en || subject.description?.en">
            <strong>{{ t('subject.fields.description_en') }}:</strong>
            <p>{{ subject.description_en || subject.description?.en }}</p>
          </div>
          <div v-if="subject.description_ar || subject.description?.ar">
            <strong>{{ t('subject.fields.description_ar') }}:</strong>
            <p>{{ subject.description_ar || subject.description?.ar }}</p>
          </div>
          <div v-if="!subject.description_en && !subject.description_ar && !subject.description?.en && !subject.description?.ar" class="text-disabled">
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
