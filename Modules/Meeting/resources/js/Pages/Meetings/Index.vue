<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import MeetingCard from '../../components/MeetingCard.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  meetings: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  teachers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')
const teacherId = ref(props.filters.teacher_id ?? null)
const status = ref(props.filters.status ?? null)
const date = ref(props.filters.date ?? null)

function buildQuery(extra = {}) {
  return {
    search: search.value,
    teacher_id: teacherId.value,
    status: status.value,
    date: date.value,
    ...extra,
  }
}

function applyFilters() {
  router.get('/meetings/meetings', buildQuery(), {
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

watch([teacherId, status, date], applyFilters)

function goToPage(page) {
  router.get('/meetings/meetings', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('meetings.title')" />

  <AppPageHeader
    :title="t('meetings.title')"
    :subtitle="t('meetings.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :href="'/meetings/meetings/create'"
        :as="Link"
      >
        {{ t('meetings.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        maxlength="100"
        :label="t('meetings.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 320px; flex: 1 1 240px;"
      />


      <VSelect
        v-model="status"
        :label="t('meetings.status')"
        :items="[
          { title: t('meetings.statuses.scheduled'), value: 'scheduled' },
          { title: t('meetings.statuses.live'), value: 'live' },
          { title: t('meetings.statuses.finished'), value: 'finished' },
          { title: t('meetings.statuses.cancelled'), value: 'cancelled' },
        ]"
        density="compact"
        clearable
        style="max-inline-size: 180px;"
      />

      <AppDateField
        v-model="date"
        :label="t('meetings.date')"
        density="compact"
        clearable
        style="max-inline-size: 180px;"
      />
    </VCardText>

    <VDivider />

    <VCardText>
      <VRow v-if="meetings.data.length">
        <VCol
          v-for="meeting in meetings.data"
          :key="meeting.id"
          cols="12"
          md="6"
          lg="4"
        >
          <MeetingCard :meeting="meeting" />
        </VCol>
      </VRow>

      <AppEmptyState v-else
          icon="bx-video"
          :title="t('meetings.empty')"
        />
    </VCardText>

    <AppPagination
      :meta="meetings.meta"
      @change="goToPage"
    />
  </VCard>
</template>
