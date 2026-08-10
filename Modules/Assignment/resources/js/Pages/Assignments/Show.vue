<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  assignment: { type: Object, required: true },
})

const { t } = useI18n()

const quickActions = [
  { key: 'edit', icon: 'bx-edit', href: `/assignments/assignments/${props.assignment.id}/edit` },
]

const gradeDialog = ref(false)
const gradingSubmission = ref(null)

const gradeForm = useForm({
  score: 0,
  grade: '',
  feedback: '',
})

function openGradeDialog(submission) {
  gradingSubmission.value = submission
  gradeForm.score = submission.score ?? 0
  gradeForm.grade = submission.grade ?? ''
  gradeForm.feedback = submission.feedback ?? ''
  gradeDialog.value = true
}

function submitGrade() {
  gradeForm.patch(`/assignments/assignments/${props.assignment.id}/submissions/${gradingSubmission.value.id}/grade`, {
    preserveScroll: true,
    onSuccess: () => {
      gradeDialog.value = false
    },
  })
}
</script>

<template>
  <Head :title="assignment.title" />

  <VRow>
    <!-- 👉 Header banner -->
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar color="primary" variant="tonal" size="56">
            <VIcon icon="bx-task" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ assignment.title }}
              </h5>
              <VChip :color="assignment.is_published ? 'success' : 'secondary'" size="small">
                {{ assignment.is_published ? t('assignments.published') : t('assignments.unpublished') }}
              </VChip>
              <VChip v-if="assignment.is_group_work" color="info" size="small" variant="tonal">
                {{ t('assignments.isGroupWork') }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ assignment.subject?.name ?? '—' }}
              <template v-if="assignment.class">
                · {{ assignment.class.name }}
              </template>
              · {{ assignment.teacher?.name ?? '—' }}
            </p>
          </div>

          <VBtn
            prepend-icon="bx-edit"
            :href="`/assignments/assignments/${assignment.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Details -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-detail" />
          </template>
          <VCardTitle>{{ t('assignments.sections.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('assignments.description') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ assignment.description ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VRow no-gutters>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('assignments.academicYear') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ assignment.academic_year?.name ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('assignments.semester') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ assignment.semester?.name ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
            </VRow>
          </VList>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Grading & Dates -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-medal" />
          </template>
          <VCardTitle>{{ t('assignments.sections.grading') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('assignments.maxScore') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ assignment.max_score }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('assignments.deadline') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ assignment.deadline ? new Date(assignment.deadline).toLocaleString() : '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('assignments.assignmentType') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ assignment.assignment_type ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('assignments.publishedAt') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ assignment.published_at ? new Date(assignment.published_at).toLocaleString() : '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Submissions -->
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-upload" />
          </template>
          <VCardTitle>{{ t('assignments.submissions') }}</VCardTitle>
        </VCardItem>

        <VCardText v-if="!assignment.submissions || assignment.submissions.length === 0" class="text-disabled">
          {{ t('assignments.noSubmissions') }}
        </VCardText>

        <VTable v-else>
          <thead>
            <tr>
              <th>{{ t('assignments.student') }}</th>
              <th>{{ t('assignments.submittedAt') }}</th>
              <th>{{ t('assignments.late') }}</th>
              <th>{{ t('assignments.score') }}</th>
              <th>{{ t('assignments.grade') }}</th>
              <th />
            </tr>
          </thead>
          <tbody>
            <tr v-for="submission in assignment.submissions" :key="submission.id">
              <td>{{ submission.student_name ?? '—' }}</td>
              <td>{{ submission.submitted_at ? new Date(submission.submitted_at).toLocaleString() : '—' }}</td>
              <td>
                <VChip :color="submission.is_late ? 'error' : 'success'" size="small">
                  {{ submission.is_late ? t('assignments.late') : t('assignments.onTime') }}
                </VChip>
              </td>
              <td>{{ submission.score ?? '—' }}</td>
              <td>{{ submission.grade ?? '—' }}</td>
              <td class="text-end">
                <VBtn size="small" variant="tonal" @click="openGradeDialog(submission)">
                  {{ t('assignments.gradeAction') }}
                </VBtn>
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>

    <!-- 👉 Quick Actions -->
    <VCol cols="12" md="4">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-bolt-circle" />
          </template>
          <VCardTitle>{{ t('assignments.quickActions') }}</VCardTitle>
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
            <VListItemTitle>{{ t(`assignments.quickAction.${action.key}`) }}</VListItemTitle>
          </VListItem>
        </VList>
      </VCard>
    </VCol>
  </VRow>

  <VDialog v-model="gradeDialog" max-width="480">
  <AppPageHeader
    :title="t('assignments.gradeAction')"
  />

  <VCard>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VTextField
              v-model="gradeForm.score"
              type="number"
              min="0"
              :label="t('assignments.score')"
              :error-messages="gradeForm.errors.score"
            />
          </VCol>
          <VCol cols="12">
            <VTextField
              v-model="gradeForm.grade"
              :label="t('assignments.grade')"
              :error-messages="gradeForm.errors.grade"
            />
          </VCol>
          <VCol cols="12">
            <VTextarea
              v-model="gradeForm.feedback"
              :label="t('assignments.feedback')"
              :error-messages="gradeForm.errors.feedback"
              rows="3"
            />
          </VCol>
        </VRow>
      </VCardText>
      <VCardText class="d-flex justify-end gap-3">
        <VBtn variant="tonal" color="secondary" @click="gradeDialog = false">
          {{ t('common.cancel') }}
        </VBtn>
        <VBtn :loading="gradeForm.processing" @click="submitGrade">
          {{ t('common.save') }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
