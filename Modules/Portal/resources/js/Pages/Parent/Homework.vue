<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import ChildSelector from '@modules/Portal/resources/js/components/ChildSelector.vue'

defineOptions({ layout: ParentLayout })

const props = defineProps({
  assignments: { type: Object, required: true },
  submissions: { type: Object, default: () => ({}) },
  children: { type: Array, default: () => [] },
  selectedChild: { type: [Number, null], default: null },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')

let searchTimer
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/parent/homework', { search: search.value }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 350)
})

const headers = computed(() => [
  { title: t('portal.parent.homeworkTitle'), key: 'title' },
  { title: t('portal.parent.deadline'), key: 'deadline' },
  { title: t('portal.parent.status'), key: 'status' },
])

function submissionFor(id) {
  return props.submissions[id] ?? null
}

function statusOf(item) {
  const submission = submissionFor(item.id)

  if (!submission)
    return { label: t('portal.parent.notSubmitted'), color: 'warning' }

  if (submission.is_graded)
    return { label: `${submission.score ?? '—'}`, color: 'success' }

  return { label: t('portal.parent.submitted'), color: 'info' }
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString() : '—'
}

function goToPage(page) {
  router.get('/parent/homework', { search: search.value, page }, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('portal.nav.homework')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.homework')"
        :subtitle="t('portal.parent.homeworkSubtitle')"
        icon="bx-task"
      >
        <template #actions>
          <ChildSelector
            :children="children"
            :selected="selectedChild"
            url="/parent/homework"
          />
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <VTextField
            v-model="search"
            :label="t('portal.parent.searchHomework')"
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

          <template #no-data>
            <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
              <VIcon icon="bx-task" size="40" class="mb-2" />
              {{ t('portal.parent.noHomework') }}
            </div>
          </template>
        </VDataTable>

        <template v-if="assignments.meta && assignments.meta.last_page > 1">
          <VDivider />
          <div class="d-flex justify-end pa-4">
            <VPagination
              :model-value="assignments.meta.current_page"
              :length="assignments.meta.last_page"
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
