<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import StudentLayout from '@modules/Portal/resources/js/layouts/student.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

defineOptions({ layout: StudentLayout })

const props = defineProps({
  meetings: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

/*
 * URL state, debounce and paging come from the shared composable rather than
 * a per-page copy of all three.
 */
const { filters, goToPage } = useIndexFilters({
  url: '/student/meetings',
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
  { title: t('portal.student.meetingTitle'), key: 'title' },
  { title: t('portal.student.scheduledAt'), key: 'scheduled_at' },
  { title: t('portal.student.duration'), key: 'duration_minutes', align: 'end' },
  { title: t('portal.student.status'), key: 'status' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function formatDateTime(value) {
  return value ? new Date(value).toLocaleString() : '—'
}

</script>

<template>
  <Head :title="t('portal.nav.meetings')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.meetings')"
        :subtitle="t('portal.student.meetingsSubtitle')"
        icon="bx-video"
      />
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <VTextField
            v-model="filters.search.value"
            maxlength="100"
            :label="t('portal.student.searchMeetings')"
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
              v-if="item.meeting_url && item.status === 'live'"
              variant="tonal"
              color="success"
              size="small"
              prepend-icon="bx-video"
              :href="item.meeting_url"
              target="_blank"
              rel="noopener noreferrer"
            >
              {{ t('portal.student.join') }}
            </VBtn>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-video"
          :title="t('portal.student.noMeetings')"
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
