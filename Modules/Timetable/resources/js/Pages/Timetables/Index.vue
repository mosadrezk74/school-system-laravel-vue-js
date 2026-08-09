<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  timetables: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const headers = computed(() => [
  { title: t('timetables.class'), key: 'class' },
  { title: t('timetables.academicYear'), key: 'academic_year' },
  { title: t('timetables.semester'), key: 'semester' },
  { title: t('common.active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  if (!confirm(t('timetables.confirmArchive')))
    return

  router.delete(`/schedule/timetables/${item.id}`, { preserveScroll: true })
}

function goToPage(page) {
  router.get('/schedule/timetables', { ...props.filters, page }, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('timetables.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('timetables.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('timetables.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :href="'/schedule/timetables/create'"
          :as="Link"
        >
          {{ t('timetables.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VDataTable
      :headers="headers"
      :items="timetables.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.class="{ item }">
        {{ item.class?.name }}
      </template>

      <template #item.academic_year="{ item }">
        {{ item.academic_year?.name }}
      </template>

      <template #item.semester="{ item }">
        {{ item.semester?.name ?? '-' }}
      </template>

      <template #item.is_active="{ item }">
        <VChip :color="item.is_active ? 'success' : 'secondary'" size="small">
          {{ item.is_active ? t('common.active') : t('common.no') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/schedule/timetables/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-grid-alt" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-calendar-week" size="40" class="mb-2" />
          {{ t('timetables.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="timetables.meta && timetables.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="timetables.meta.current_page"
          :length="timetables.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
