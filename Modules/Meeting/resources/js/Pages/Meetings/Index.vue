<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import MeetingCard from '../../components/MeetingCard.vue'

const props = defineProps({
  meetings: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  teachers: { type: Array, default: () => [] },
  branches: { type: Array, default: () => [] },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')
const teacherId = ref(props.filters.teacher_id ?? null)
const branchId = ref(props.filters.branch_id ?? null)
const status = ref(props.filters.status ?? null)
const date = ref(props.filters.date ?? null)

function buildQuery(extra = {}) {
  return {
    search: search.value,
    teacher_id: teacherId.value,
    branch_id: branchId.value,
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

watch([teacherId, branchId, status, date], applyFilters)

function goToPage(page) {
  router.get('/meetings/meetings', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('meetings.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('meetings.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('meetings.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :href="'/meetings/meetings/create'"
          :as="Link"
        >
          {{ t('meetings.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('meetings.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 320px; flex: 1 1 240px;"
      />

      <VSelect
        v-model="teacherId"
        :label="t('meetings.teacher')"
        :items="teachers.map(u => ({ title: u.name, value: u.id }))"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <VSelect
        v-model="branchId"
        :label="t('meetings.branch')"
        :items="branches.map(b => ({ title: b.name, value: b.id }))"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
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

      <VTextField
        v-model="date"
        type="date"
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

      <div v-else class="d-flex flex-column align-center pa-8 text-center text-disabled">
        <VIcon icon="bx-video" size="40" class="mb-2" />
        {{ t('meetings.empty') }}
      </div>
    </VCardText>

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
</template>
