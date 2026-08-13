<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'

defineOptions({ layout: TeacherLayout })

const props = defineProps({
  meetings: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

/*
 * URL state, debounce and paging come from the shared composable rather than
 * a per-page copy of all three.
 */
const { filters, goToPage } = useIndexFilters({
  url: '/teacher/meetings',
  initial: {
    search: props.filters.search ?? null,
    status: props.filters.status ?? null,
  },
})

const statusColors = {
  scheduled: 'info',
  live: 'success',
  ended: 'secondary',
  cancelled: 'error',
}

const headers = computed(() => [
  { title: t('portal.teacher.meetingTitle'), key: 'title' },
  { title: t('portal.teacher.scheduledAt'), key: 'scheduled_at' },
  { title: t('portal.teacher.duration'), key: 'duration_minutes', align: 'end' },
  { title: t('portal.teacher.status'), key: 'status' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function formatDateTime(value) {
  return value ? new Date(value).toLocaleString() : '—'
}

function start(item) {
  router.patch(`/teacher/meetings/${item.id}/start`, {}, { preserveScroll: true })
}

function cancel(item) {
  askConfirm({
    message: t('portal.teacher.confirmCancel'),
    color: 'error',
    onConfirm: () => {
      router.patch(`/teacher/meetings/${item.id}/cancel`, {}, { preserveScroll: true })
    },
  })
}

/*
 * Ending the meeting closes the room for everyone still inside it, so it is
 * confirmed the same way cancelling is.
 */
function finish(item) {
  askConfirm({
    message: t('portal.teacher.confirmFinish'),
    color: 'error',
    onConfirm: () => {
      router.patch(`/teacher/meetings/${item.id}/finish`, {}, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('portal.nav.meetings')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.meetings')"
        :subtitle="t('portal.teacher.meetingsSubtitle')"
        icon="bx-video"
      >
        <template #actions>
          <VBtn prepend-icon="bx-plus" href="/teacher/meetings/create" :as="Link">
            {{ t('portal.teacher.newMeeting') }}
          </VBtn>
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <VTextField
            v-model="filters.search.value"
            maxlength="100"
            :label="t('portal.teacher.searchMeetings')"
            prepend-inner-icon="bx-search"
            density="compact"
            clearable
            hide-details
            style="max-inline-size: 320px;"
          />
        </VCardText>

        <VDataTable
          :headers="headers"
          :items="meetings.data"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.scheduled_at="{ item }">
            {{ formatDateTime(item.scheduled_at) }}
          </template>

          <template #item.status="{ item }">
            <VChip
              :color="statusColors[item.status] ?? 'secondary'"
              size="small"
              variant="tonal"
            >
              {{ item.status ?? '—' }}
            </VChip>
          </template>

          <template #item.actions="{ item }">
            <VBtn
              v-if="item.status === 'scheduled'"
              variant="tonal"
              size="small"
              prepend-icon="bx-play"
              class="me-2"
              @click="start(item)"
            >
              {{ t('portal.teacher.start') }}
            </VBtn>
            <!-- The in-app room, not meet.etqan.cloud: leaving the app would
                 skip the embed and its lifecycle handling. -->
            <VBtn
              v-if="item.status === 'live'"
              variant="tonal"
              color="success"
              size="small"
              prepend-icon="bx-video"
              class="me-2"
              :href="`/meetings/meetings/${item.id}/join`"
              :as="Link"
            >
              {{ t('portal.teacher.join') }}
            </VBtn>
            <VBtn
              v-if="item.status === 'live'"
              variant="tonal"
              color="error"
              size="small"
              prepend-icon="bx-stop-circle"
              class="me-2"
              @click="finish(item)"
            >
              {{ t('portal.teacher.finish') }}
            </VBtn>
            <IconBtn :href="`/teacher/meetings/${item.id}/edit`" :as="Link">
              <VIcon icon="bx-edit" />
            </IconBtn>
            <IconBtn v-if="item.status === 'scheduled'" @click="cancel(item)">
              <VIcon icon="bx-x-circle" />
            </IconBtn>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-video"
          :title="t('portal.teacher.noMeetings')"
        />
      </template>
        </VDataTable>

    <AppPagination
      :meta="meetings.meta"
      @change="goToPage"
    />
      </VCard>
    </VCol>
  </VRow>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
