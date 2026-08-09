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
  results: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const headers = computed(() => [
  { title: t('portal.student.exam'), key: 'exam' },
  { title: t('portal.student.subject'), key: 'subject' },
  { title: t('portal.student.marks'), key: 'obtained_marks', align: 'end' },
  { title: t('portal.student.percentage'), key: 'percentage', align: 'end' },
  { title: t('portal.student.grade'), key: 'grade' },
  { title: t('portal.student.result'), key: 'is_passed' },
])

const average = computed(() => {
  const rows = props.results.data ?? []

  if (!rows.length)
    return 0

  const sum = rows.reduce((total, row) => total + Number(row.percentage ?? 0), 0)

  return Math.round((sum / rows.length) * 10) / 10
})

function goToPage(page) {
  router.get('/student/grades', { page }, { preserveState: true, preserveScroll: true })
}
</script>

<template>
  <Head :title="t('portal.nav.grades')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.grades')"
        :subtitle="t('portal.student.gradesSubtitle')"
        icon="bx-medal"
      >
        <template #actions>
          <VChip color="primary" variant="tonal" size="large">
            {{ t('portal.student.average') }}: {{ average }}%
          </VChip>
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
              {{ item.is_passed ? t('portal.student.passed') : t('portal.student.failed') }}
            </VChip>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-medal"
          :title="t('portal.student.noResults')"
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
