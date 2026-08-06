<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  meeting: { type: Object, required: true },
  can: { type: Object, default: () => ({ manage: false }) },
})

const { t } = useI18n()

const statusColor = {
  scheduled: 'secondary',
  live: 'success',
  finished: 'info',
  cancelled: 'error',
}

function start() {
  router.patch(`/meetings/meetings/${props.meeting.id}/start`, {}, { preserveScroll: true })
}

function finish() {
  if (!confirm(t('meetings.confirmFinish')))
    return

  router.patch(`/meetings/meetings/${props.meeting.id}/finish`, {}, { preserveScroll: true })
}

function cancel() {
  if (!confirm(t('meetings.confirmCancel')))
    return

  router.patch(`/meetings/meetings/${props.meeting.id}/cancel`, {}, { preserveScroll: true })
}

function join() {
  window.open(props.meeting.meeting_url, '_blank', 'noopener')
}

function copyLink() {
  navigator.clipboard?.writeText(props.meeting.meeting_url)
}
</script>

<template>
  <Head :title="meeting.title" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar color="primary" variant="tonal" size="56">
            <VIcon icon="bx-video" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ meeting.title }}
              </h5>
              <VChip :color="statusColor[meeting.status]" size="small">
                {{ t(`meetings.statuses.${meeting.status}`) }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ meeting.teacher?.name ?? '—' }}
              <template v-if="meeting.branch">
                · {{ meeting.branch.name }}
              </template>
            </p>
          </div>

          <VBtn
            v-if="can.manage && meeting.status === 'scheduled'"
            variant="tonal"
            prepend-icon="bx-edit"
            :href="`/meetings/meetings/${meeting.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-detail" />
          </template>
          <VCardTitle>{{ t('meetings.sections.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('meetings.description') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ meeting.description || '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VRow no-gutters>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('meetings.academicYear') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ meeting.academic_year?.name ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('meetings.semester') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ meeting.semester?.name ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
            </VRow>
          </VList>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-calendar" />
          </template>
          <VCardTitle>{{ t('meetings.sections.schedule') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('meetings.scheduledAt') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ meeting.scheduled_at ? new Date(meeting.scheduled_at).toLocaleString() : '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('meetings.durationMinutes') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ meeting.duration_minutes }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('meetings.roomName') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ meeting.room_name }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('meetings.hasPassword') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ meeting.has_password ? t('common.yes') : t('common.no') }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-bolt-circle" />
          </template>
          <VCardTitle>{{ t('meetings.quickActions') }}</VCardTitle>
        </VCardItem>
        <VCardText class="d-flex flex-wrap gap-3">
          <VBtn
            v-if="meeting.status === 'live'"
            color="success"
            prepend-icon="bx-log-in-circle"
            @click="join"
          >
            {{ t('meetings.join') }}
          </VBtn>

          <VBtn
            v-if="can.manage && meeting.status === 'scheduled'"
            variant="tonal"
            color="success"
            prepend-icon="bx-play-circle"
            @click="start"
          >
            {{ t('meetings.start') }}
          </VBtn>

          <VBtn
            v-if="can.manage && meeting.status === 'live'"
            variant="tonal"
            color="info"
            prepend-icon="bx-stop-circle"
            @click="finish"
          >
            {{ t('meetings.finish') }}
          </VBtn>

          <VBtn
            v-if="can.manage && meeting.status === 'scheduled'"
            variant="tonal"
            color="error"
            prepend-icon="bx-x-circle"
            @click="cancel"
          >
            {{ t('meetings.cancel') }}
          </VBtn>

          <VBtn
            variant="text"
            prepend-icon="bx-link"
            @click="copyLink"
          >
            {{ t('meetings.copyLink') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
