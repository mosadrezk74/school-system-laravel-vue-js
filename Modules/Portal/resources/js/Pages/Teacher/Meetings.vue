<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'

defineOptions({ layout: TeacherLayout })

const props = defineProps({
  meetings: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')

function buildQuery(extra = {}) {
  return { search: search.value, ...extra }
}

function applyFilters() {
  router.get('/teacher/meetings', buildQuery(), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

let searchTimer
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 350)
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
  if (!confirm(t('portal.teacher.confirmCancel')))
    return

  router.patch(`/teacher/meetings/${item.id}/cancel`, {}, { preserveScroll: true })
}

function goToPage(page) {
  router.get('/teacher/meetings', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
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
            v-model="search"
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
            <VBtn
              v-if="item.meeting_url && item.status === 'live'"
              variant="tonal"
              color="success"
              size="small"
              prepend-icon="bx-video"
              class="me-2"
              :href="item.meeting_url"
              target="_blank"
              rel="noopener noreferrer"
            >
              {{ t('portal.teacher.join') }}
            </VBtn>
            <IconBtn :href="`/teacher/meetings/${item.id}/edit`" :as="Link">
              <VIcon icon="bx-edit" />
            </IconBtn>
            <IconBtn v-if="item.status === 'scheduled'" @click="cancel(item)">
              <VIcon icon="bx-x-circle" />
            </IconBtn>
          </template>

          <template #no-data>
            <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
              <VIcon icon="bx-video" size="40" class="mb-2" />
              {{ t('portal.teacher.noMeetings') }}
            </div>
          </template>
        </VDataTable>

        <template v-if="meetings.meta && meetings.meta.last_page > 1">
          <VDivider />
          <div class="d-flex justify-end pa-4">
            <VPagination
              :model-value="meetings.meta.current_page"
              :length="meetings.meta.last_page"
              :total-visible="5"
              density="compact"
              @update:model-value="goToPage"
            />
          </div>
        </template>
      </VCard>
    </VCol>
  </VRow>
</template>
