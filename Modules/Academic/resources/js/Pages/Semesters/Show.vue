<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  semester: { type: Object, required: true },
})

const { t } = useI18n()
const page = usePage()

// The enabled languages are shared globally, so stored translations are listed
// in the school's own language order rather than JSON key order.
const languages = computed(() => page.props.languages ?? [])

const nameTranslations = computed(() => languages.value
  .map(language => ({ language, value: props.semester.name_translations?.[language.locale] }))
  .filter(entry => entry.value))

const details = computed(() => [
  { key: 'academicYear', label: t('semesters.fields.academicYear'), value: props.semester.academic_year?.name ?? '—' },
  { key: 'sequence', label: t('semesters.fields.sequence'), value: props.semester.sequence ?? '—' },
  { key: 'startDate', label: t('semesters.fields.startDate'), value: props.semester.start_date ?? '—' },
  { key: 'endDate', label: t('semesters.fields.endDate'), value: props.semester.end_date ?? '—' },
  { key: 'examStartDate', label: t('semesters.fields.examStartDate'), value: props.semester.exam_start_date ?? '—' },
  { key: 'examEndDate', label: t('semesters.fields.examEndDate'), value: props.semester.exam_end_date ?? '—' },
])
</script>

<template>
  <Head :title="semester.name" />

  <VCard>
    <VCardItem>
      <VCardTitle class="d-flex align-center gap-2">
        {{ semester.name }}
        <VChip :color="semester.is_active ? 'success' : 'secondary'" size="small">
          {{ semester.is_active ? t('common.active') : t('common.inactive') }}
        </VChip>
      </VCardTitle>
      <VCardSubtitle>{{ semester.academic_year?.name ?? '—' }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-edit"
          :href="`/academic/semesters/${semester.id}/edit`"
          :as="Link"
          color="primary"
          variant="tonal"
        >
          {{ t('common.edit') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText>
      <h6 class="text-h6 mb-3">
        {{ t('semesters.fields.name') }}
      </h6>
      <VRow no-gutters class="mb-4">
        <VCol
          v-for="entry in nameTranslations"
          :key="entry.language.locale"
          cols="12"
          md="6"
        >
          <VListItem>
            <VListItemTitle class="text-medium-emphasis text-caption">
              {{ entry.language.name }}
            </VListItemTitle>
            <VListItemSubtitle class="text-body-1 text-high-emphasis">
              {{ entry.value }}
            </VListItemSubtitle>
          </VListItem>
        </VCol>
      </VRow>

      <VDivider class="mb-4" />

      <VRow no-gutters>
        <VCol
          v-for="detail in details"
          :key="detail.key"
          cols="12"
          md="6"
        >
          <VListItem>
            <VListItemTitle class="text-medium-emphasis text-caption">
              {{ detail.label }}
            </VListItemTitle>
            <VListItemSubtitle class="text-body-1 text-high-emphasis">
              {{ detail.value }}
            </VListItemSubtitle>
          </VListItem>
        </VCol>
      </VRow>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end">
      <VBtn
        variant="tonal"
        color="secondary"
        href="/academic/semesters"
        :as="Link"
      >
        {{ t('common.back') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
