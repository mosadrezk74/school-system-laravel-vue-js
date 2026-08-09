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
  results: { type: Object, required: true },
  children: { type: Array, default: () => [] },
  selectedChild: { type: [Number, null], default: null },
})

const { t } = useI18n()

const headers = computed(() => [
  { title: t('portal.parent.exam'), key: 'exam' },
  { title: t('portal.parent.subject'), key: 'subject' },
  { title: t('portal.parent.marks'), key: 'obtained_marks', align: 'end' },
  { title: t('portal.parent.percentage'), key: 'percentage', align: 'end' },
  { title: t('portal.parent.grade'), key: 'grade' },
  { title: t('portal.parent.result'), key: 'is_passed' },
])

const average = computed(() => {
  const rows = props.results.data ?? []

  if (!rows.length)
    return 0

  const sum = rows.reduce((total, row) => total + Number(row.percentage ?? 0), 0)

  return Math.round((sum / rows.length) * 10) / 10
})

function goToPage(page) {
  router.get('/parent/grades', { page }, { preserveState: true, preserveScroll: true })
}
</script>

<template>
  <Head :title="t('portal.nav.grades')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.grades')"
        :subtitle="t('portal.parent.gradesSubtitle')"
        icon="bx-medal"
      >
        <template #actions>
          <div class="d-flex align-center gap-3">
            <ChildSelector
              :children="children"
              :selected="selectedChild"
              url="/parent/grades"
            />
            <VChip color="primary" variant="tonal" size="large">
              {{ t('portal.parent.average') }}: {{ average }}%
            </VChip>
          </div>
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VDataTable
          :headers="headers"
          :items="results.data"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.exam="{ item }">
            {{ item.exam?.name ?? '—' }}
          </template>

          <template #item.subject="{ item }">
            {{ item.subject?.name ?? '—' }}
          </template>

          <template #item.percentage="{ item }">
            {{ item.percentage !== null ? `${item.percentage}%` : '—' }}
          </template>

          <template #item.is_passed="{ item }">
            <VChip
              :color="item.is_passed ? 'success' : 'error'"
              size="small"
              variant="tonal"
            >
              {{ item.is_passed ? t('portal.parent.passed') : t('portal.parent.failed') }}
            </VChip>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-medal"
          :title="t('portal.parent.noResults')"
        />
      </template>
        </VDataTable>

    <AppPagination
      :meta="results.meta"
      @change="goToPage"
    />
      </VCard>
    </VCol>
  </VRow>
</template>
