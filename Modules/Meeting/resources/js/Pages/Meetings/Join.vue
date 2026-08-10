<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import JitsiMeeting from '../../components/JitsiMeeting.vue'

const props = defineProps({
  meeting: { type: Object, required: true },
  jitsi: { type: Object, required: true },
  can: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const page = usePage()

const conference = ref(null)
const participants = ref(0)
const copied = ref(false)

const locale = computed(() => page.props.locale ?? 'en')

const statusColor = computed(() => ({
  live: 'success',
  scheduled: 'info',
  finished: 'secondary',
  cancelled: 'error',
}[props.meeting.status] ?? 'secondary'))

const details = computed(() => [
  { label: t('meetings.teacher'), value: props.meeting.teacher?.name },
  { label: t('meetings.subject'), value: props.meeting.subject?.name },
  { label: t('meetings.schoolClass'), value: props.meeting.school_class?.name },
].filter(row => row.value))

function copyLink() {
  navigator.clipboard?.writeText(props.meeting.meeting_url)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}

function leave() {
  // Ask Jitsi to hang up first so the conference closes cleanly, then leave.
  conference.value?.hangUp()
  router.visit('/meetings/meetings')
}
</script>

<template>
  <Head :title="meeting.title" />

  <AppPageHeader :title="meeting.title">
    <template #actions>
      <VBtn variant="tonal" color="secondary" prepend-icon="bx-link" @click="copyLink">
        {{ copied ? t('meetings.copied') : t('meetings.copyLink') }}
      </VBtn>

      <VBtn color="error" prepend-icon="bx-exit" @click="leave">
        {{ t('meetings.jitsi.leave') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VRow>
    <VCol cols="12" lg="9">
      <VCard>
        <VCardText class="pa-3">
          <JitsiMeeting
            ref="conference"
            :domain="jitsi.domain"
            :room-name="jitsi.room_name"
            :display-name="jitsi.display_name"
            :email="jitsi.email"
            :is-moderator="can.manage === true"
            :lang="locale"
            @participant-joined="participants++"
            @participant-left="participants = Math.max(0, participants - 1)"
            @closed="leave"
          />
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" lg="3">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('meetings.details') }}</VCardTitle>
          <template #append>
            <VChip :color="statusColor" size="small">
              {{ t(`meetings.statuses.${meeting.status}`) }}
            </VChip>
          </template>
        </VCardItem>

        <VCardText>
          <div
            v-for="row in details"
            :key="row.label"
            class="d-flex justify-space-between gap-3 py-2 border-b"
          >
            <span class="text-caption text-medium-emphasis">{{ row.label }}</span>
            <span class="text-body-2 text-end">{{ row.value }}</span>
          </div>

          <div class="d-flex justify-space-between gap-3 py-2">
            <span class="text-caption text-medium-emphasis">{{ t('meetings.jitsi.participants') }}</span>
            <span class="text-body-2">{{ participants }}</span>
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
