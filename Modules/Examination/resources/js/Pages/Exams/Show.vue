<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  exam: { type: Object, required: true },
})

const { t } = useI18n()

const quickActions = [
  { key: 'edit', icon: 'bx-edit', href: `/examination/exams/${props.exam.id}/edit` },
]
</script>

<template>
  <Head :title="exam.name" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar color="primary" variant="tonal" size="56">
            <VIcon icon="bx-notepad" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ exam.name }}
              </h5>
              <VChip :color="exam.is_published ? 'success' : 'secondary'" size="small">
                {{ exam.is_published ? t('exams.published') : t('exams.unpublished') }}
              </VChip>
              <VChip color="info" size="small" variant="tonal">
                {{ t(`exams.types.${exam.exam_type}`, exam.exam_type) }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ exam.academic_year?.name ?? '—' }}
              <template v-if="exam.semester">
                · {{ exam.semester.name }}
              </template>
              <template v-if="exam.class">
                · {{ exam.class.name }}
              </template>
            </p>
          </div>

          <VBtn
            prepend-icon="bx-edit"
            :href="`/examination/exams/${exam.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="8">
      <VCard class="mb-6">
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-calendar" />
          </template>
          <VCardTitle>{{ t('exams.sections.schedule') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('exams.startDate') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ exam.start_date ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('exams.endDate') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ exam.end_date ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('exams.passingGrade') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ exam.passing_grade }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('exams.totalMarks') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ exam.total_marks }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-book" />
          </template>
          <VCardTitle>{{ t('exams.subjectsSection') }}</VCardTitle>
        </VCardItem>

        <VCardText v-if="!exam.exam_subjects || exam.exam_subjects.length === 0" class="text-disabled">
          {{ t('exams.noSubjects') }}
        </VCardText>

        <VTable v-else>
          <thead>
            <tr>
              <th>{{ t('exams.subject') }}</th>
              <th>{{ t('exams.class') }}</th>
              <th>{{ t('exams.subjectTotalMarks') }}</th>
              <th>{{ t('exams.passingMarks') }}</th>
              <th>{{ t('exams.examDate') }}</th>
              <th />
            </tr>
          </thead>
          <tbody>
            <tr v-for="examSubject in exam.exam_subjects" :key="examSubject.id">
              <td>{{ examSubject.subject_name ?? '—' }}</td>
              <td>{{ examSubject.class_name ?? '—' }}</td>
              <td>{{ examSubject.total_marks }}</td>
              <td>{{ examSubject.passing_marks }}</td>
              <td>{{ examSubject.exam_date ?? '—' }}</td>
              <td class="text-end">
                <VBtn
                  size="small"
                  variant="tonal"
                  :href="`/examination/exams/${exam.id}/subjects/${examSubject.id}/results`"
                  :as="Link"
                >
                  {{ t('exams.enterResults') }}
                </VBtn>
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-bolt-circle" />
          </template>
          <VCardTitle>{{ t('exams.quickActions') }}</VCardTitle>
        </VCardItem>
        <VList density="compact" class="pa-0">
          <VListItem
            v-for="action in quickActions"
            :key="action.key"
            :href="action.href"
            :as="Link"
          >
            <template #prepend>
              <VIcon :icon="action.icon" size="20" />
            </template>
            <VListItemTitle>{{ t(`exams.quickAction.${action.key}`) }}</VListItemTitle>
          </VListItem>
        </VList>
      </VCard>
    </VCol>
  </VRow>
</template>
