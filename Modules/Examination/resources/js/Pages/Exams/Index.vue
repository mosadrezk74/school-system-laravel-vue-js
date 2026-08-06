<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  exams: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

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
  if (!confirm(t('exams.confirmArchive')))
    return

  router.delete(`/examination/exams/${item.id}`, { preserveScroll: true })
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('exams.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('exams.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :to="'/examination/exams/create'"
          :href="'/examination/exams/create'"
          :as="Link"
        >
          {{ t('exams.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

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
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-notepad" size="40" class="mb-2" />
          {{ t('exams.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="exams.meta && exams.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="exams.meta.current_page"
          :length="exams.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
