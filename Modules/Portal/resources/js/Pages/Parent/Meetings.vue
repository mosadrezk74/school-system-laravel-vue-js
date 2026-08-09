<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import ChildSelector from '@modules/Portal/resources/js/components/ChildSelector.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

defineOptions({ layout: ParentLayout })

const props = defineProps({
  meetings: { type: Object, required: true },
  children: { type: Array, default: () => [] },
  selectedChild: { type: [Number, null], default: null },
})

const { t } = useI18n()

const statusColors = {
  scheduled: 'info',
  live: 'success',
  ended: 'secondary',
  cancelled: 'error',
}

const headers = computed(() => [
  { title: t('portal.parent.meetingTitle'), key: 'title' },
  { title: t('portal.parent.scheduledAt'), key: 'scheduled_at' },
  { title: t('portal.parent.duration'), key: 'duration_minutes', align: 'end' },
  { title: t('portal.parent.status'), key: 'status' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function formatDateTime(value) {
  return value ? new Date(value).toLocaleString() : '—'
}


function goToPage(page) {
  router.get('/parent/meetings', { page }, {
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
        :subtitle="t('portal.parent.meetingsSubtitle')"
        icon="bx-video"
      >
        <template #actions>
          <ChildSelector :children="children" :selected="selectedChild" url="/parent/meetings" />
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <VCard>
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
              v-if="item.meeting_url && item.status === 'live'"
              variant="tonal"
              color="success"
              size="small"
              prepend-icon="bx-video"
              :href="item.meeting_url"
              target="_blank"
              rel="noopener noreferrer"
            >
              {{ t('portal.parent.join') }}
            </VBtn>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-video"
          :title="t('portal.parent.noMeetings')"
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
</template>
