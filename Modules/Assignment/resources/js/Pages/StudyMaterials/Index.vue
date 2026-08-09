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
  studyMaterials: { type: Object, required: true },
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
  router.get('/assignments/study-materials', buildQuery(), {
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
  { title: t('studyMaterials.title'), key: 'title' },
  { title: t('studyMaterials.subject'), key: 'subject' },
  { title: t('studyMaterials.teacher'), key: 'teacher' },
  { title: t('studyMaterials.materialType'), key: 'material_type' },
  { title: t('studyMaterials.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('studyMaterials.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/assignments/study-materials/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/assignments/study-materials', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('studyMaterials.title')" />

  <AppPageHeader
    :title="t('studyMaterials.title')"
    :subtitle="t('studyMaterials.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :to="'/assignments/study-materials/create'"
        :href="'/assignments/study-materials/create'"
        :as="Link"
      >
        {{ t('studyMaterials.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('studyMaterials.search')"
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
          { title: t('studyMaterials.published'), value: true },
          { title: t('studyMaterials.unpublished'), value: false },
        ]"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="studyMaterials.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.subject="{ item }">
        {{ item.subject?.name ?? '—' }}
      </template>

      <template #item.teacher="{ item }">
        {{ item.teacher?.name ?? '—' }}
      </template>

      <template #item.material_type="{ item }">
        {{ item.material_type ?? '—' }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_published ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_published ? t('studyMaterials.published') : t('studyMaterials.unpublished') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/assignments/study-materials/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/assignments/study-materials/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-book-content"
          :title="t('studyMaterials.empty')"
        />
      </template>
    </VDataTable>

    <AppPagination
      :meta="studyMaterials.meta"
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
