<script setup>
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  meeting: { type: Object, required: true },
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

function join() {
  window.open(props.meeting.meeting_url, '_blank', 'noopener')
}

function copyLink() {
  navigator.clipboard?.writeText(props.meeting.meeting_url)
}
</script>

<template>
  <VCard>
    <VCardItem>
      <template #prepend>
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-video" />
        </VAvatar>
      </template>

      <VCardTitle>{{ meeting.title }}</VCardTitle>
      <VCardSubtitle>{{ meeting.teacher?.name ?? '—' }} · {{ meeting.branch?.name ?? '—' }}</VCardSubtitle>

      <template #append>
        <VChip :color="statusColor[meeting.status]" size="small">
          {{ t(`meetings.statuses.${meeting.status}`) }}
        </VChip>
      </template>
    </VCardItem>

    <VCardText>
      <div class="d-flex align-center gap-2 text-body-2 text-medium-emphasis">
        <VIcon icon="bx-calendar" size="18" />
        {{ meeting.scheduled_at ? new Date(meeting.scheduled_at).toLocaleString() : '—' }}
      </div>
    </VCardText>

    <VCardText class="d-flex flex-wrap gap-2">
      <VBtn
        v-if="meeting.status === 'live'"
        size="small"
        color="success"
        prepend-icon="bx-log-in-circle"
        @click="join"
      >
        {{ t('meetings.join') }}
      </VBtn>

      <VBtn
        v-if="meeting.can_manage && meeting.status === 'scheduled'"
        size="small"
        variant="tonal"
        color="success"
        prepend-icon="bx-play-circle"
        @click="start"
      >
        {{ t('meetings.start') }}
      </VBtn>

      <VBtn
        v-if="meeting.can_manage && meeting.status === 'live'"
        size="small"
        variant="tonal"
        color="info"
        prepend-icon="bx-stop-circle"
        @click="finish"
      >
        {{ t('meetings.finish') }}
      </VBtn>

      <VBtn
        v-if="meeting.can_manage"
        size="small"
        variant="text"
        prepend-icon="bx-link"
        @click="copyLink"
      >
        {{ t('meetings.copyLink') }}
      </VBtn>

      <VSpacer />

      <IconBtn v-if="meeting.can_manage" :href="`/meetings/meetings/${meeting.id}`" :as="Link">
        <VIcon icon="bx-show" />
      </IconBtn>
    </VCardText>
  </VCard>
</template>
