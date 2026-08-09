<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  exams: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const published = ref(props.filters.is_published ?? null)

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (published.value !== null)
    query.is_published = published.value

  return query
}

function applyFilters() {
  router.get('/examination/exams', buildQuery(), {
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

watch(published, applyFilters)

const headers = computed(() => [
  { title: t('exams.name'), key: 'name' },
  { title: t('exams.code'), key: 'code' },
  { title: t('exams.examType'), key: 'exam_type' },
  { title: t('exams.startDate'), key: 'start_date' },
  { title: t('exams.endDate'), key: 'end_date' },
  { title: t('exams.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('exams.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/examination/exams/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/examination/exams', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('exams.title')" />

  <AppPageHeader
    :title="t('exams.title')"
    :subtitle="t('exams.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :to="'/examination/exams/create'"
        :href="'/examination/exams/create'"
        :as="Link"
      >
        {{ t('exams.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('exams.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 360px; flex: 1 1 260px;"
      />

      <VSelect
        v-model="published"
        :label="t('common.filter')"
        :items="[
          { title: t('common.all'), value: null },
          { title: t('exams.published'), value: true },
          { title: t('exams.unpublished'), value: false },
        ]"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="exams.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.exam_type="{ item }">
        {{ t(`exams.types.${item.exam_type}`, item.exam_type) }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_published ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_published ? t('exams.published') : t('exams.unpublished') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/examination/exams/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/examination/exams/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-notepad"
          :title="t('exams.empty')"
        />
      </template>
    </VDataTable>

    <AppPagination
      :meta="exams.meta"
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
