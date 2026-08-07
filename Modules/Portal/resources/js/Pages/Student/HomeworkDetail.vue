<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import StudentLayout from '@modules/Portal/resources/js/layouts/student.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'

defineOptions({ layout: StudentLayout })

const props = defineProps({
  assignment: { type: Object, required: true },
  submission: { type: Object, default: null },
})

const { t } = useI18n()

const isSubmitted = computed(() => props.submission !== null)

const form = useForm({
  submitted_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
})

function submit() {
  form.post(`/student/homework/${props.assignment.id}/submit`)
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString() : '—'
}
</script>

<template>
  <Head :title="assignment.title" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="assignment.title"
        :subtitle="`${assignment.subject?.name ?? '—'} · ${t('portal.student.deadline')}: ${formatDate(assignment.deadline)}`"
        icon="bx-task"
      >
        <template #actions>
          <VBtn
            variant="tonal"
            color="secondary"
            href="/student/homework"
            :as="Link"
          >
            {{ t('portal.student.back') }}
          </VBtn>
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12" md="8">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('portal.student.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          {{ assignment.description ?? '—' }}
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('portal.student.mySubmission') }}</VCardTitle>
        </VCardItem>

        <VCardText v-if="isSubmitted">
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-caption text-medium-emphasis">
                {{ t('portal.student.submittedAt') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ formatDate(submission.submitted_at) }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-caption text-medium-emphasis">
                {{ t('portal.student.score') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ submission.is_graded ? (submission.score ?? '—') : t('portal.student.notGraded') }}
              </VListItemSubtitle>
            </VListItem>
          </VList>

          <VAlert
            v-if="submission.is_late"
            type="warning"
            variant="tonal"
            density="compact"
            class="mt-3"
          >
            {{ t('portal.student.late') }}
          </VAlert>
        </VCardText>

        <template v-else>
          <VCardText class="text-medium-emphasis">
            {{ t('portal.student.notSubmittedYet') }}
          </VCardText>

          <VDivider />

          <VCardText class="d-flex justify-end">
            <VBtn
              :loading="form.processing"
              prepend-icon="bx-upload"
              @click="submit"
            >
              {{ t('portal.student.submitNow') }}
            </VBtn>
          </VCardText>
        </template>
      </VCard>
    </VCol>
  </VRow>
</template>
