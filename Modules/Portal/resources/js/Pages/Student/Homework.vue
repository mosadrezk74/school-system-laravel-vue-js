<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import StudentLayout from '@modules/Portal/resources/js/layouts/student.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

defineOptions({ layout: StudentLayout })

const props = defineProps({
  assignments: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  submissions: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

/*
 * URL state, debounce and paging come from the shared composable rather than
 * a per-page copy of all three.
 */
const { filters, goToPage } = useIndexFilters({
  url: '/student/homework',
  initial: {
    search: props.filters.search ?? null,
  },
})

const headers = computed(() => [
  { title: t('portal.student.homeworkTitle'), key: 'title' },
  { title: t('portal.student.deadline'), key: 'deadline' },
  { title: t('portal.student.status'), key: 'status' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function submissionFor(id) {
  return props.submissions[id] ?? null
}

function statusOf(item) {
  const submission = submissionFor(item.id)

  if (!submission)
    return { label: t('portal.student.notSubmitted'), color: 'warning' }

  if (submission.is_graded)
    return { label: `${submission.score ?? '—'}`, color: 'success' }

  return { label: t('portal.student.submitted'), color: 'info' }
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString() : '—'
}

</script>

<template>
  <Head :title="t('portal.nav.homework')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.homework')"
        :subtitle="t('portal.student.homeworkSubtitle')"
        icon="bx-task"
      />
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <VTextField
            v-model="filters.search.value"
            maxlength="100"
            :label="t('portal.student.searchHomework')"
            prepend-inner-icon="bx-search"
            density="compact"
            clearable
            hide-details
            style="max-inline-size: 320px;"
          />
        </VCardText>

        <VDataTable
          :headers="headers"
          :items="assignments.data"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.deadline="{ item }">
            {{ formatDate(item.deadline) }}
          </template>

          <template #item.status="{ item }">
            <VChip :color="statusOf(item).color" size="small" variant="tonal">
              {{ statusOf(item).label }}
            </VChip>
          </template>

          <template #item.actions="{ item }">
            <VBtn
              variant="tonal"
              size="small"
              :href="`/student/homework/${item.id}`"
              :as="Link"
            >
              {{ t('portal.student.open') }}
            </VBtn>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-task"
          :title="t('portal.student.noHomework')"
        />
      </template>
        </VDataTable>

    <AppPagination
      :meta="assignments.meta"
      @change="goToPage"
    />
      </VCard>
    </VCol>
  </VRow>
</template>
