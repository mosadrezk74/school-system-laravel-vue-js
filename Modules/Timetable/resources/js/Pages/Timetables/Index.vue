<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  timetables: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const headers = computed(() => [
  { title: t('timetables.class'), key: 'class' },
  { title: t('timetables.academicYear'), key: 'academic_year' },
  { title: t('timetables.semester'), key: 'semester' },
  { title: t('common.active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('timetables.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/schedule/timetables/${item.id}`, { preserveScroll: true })
    },
  })
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

  <AppPageHeader
    :title="t('timetables.title')"
    :subtitle="t('timetables.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :href="'/schedule/timetables/create'"
        :as="Link"
      >
        {{ t('timetables.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

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
        <AppEmptyState
          icon="bx-calendar-week"
          :title="t('timetables.empty')"
        />
      </template>
    </VDataTable>

    <AppPagination
      :meta="timetables.meta"
      @change="goToPage"
    />
  </VCard>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
