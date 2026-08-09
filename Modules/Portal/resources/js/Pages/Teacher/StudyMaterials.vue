<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'

defineOptions({ layout: TeacherLayout })

const props = defineProps({
  resources: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  types: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const type = ref(props.filters.type ?? null)

const typeOptions = computed(() => props.types.map(item => ({ title: item.label, value: item.value })))

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (type.value)
    query.type = type.value

  return query
}

function applyFilters() {
  router.get('/teacher/study-materials', buildQuery(), {
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

watch(type, applyFilters)

const headers = computed(() => [
  { title: t('portal.teacher.materialTitle'), key: 'title' },
  { title: t('portal.teacher.materialType'), key: 'type_label' },
  { title: t('portal.teacher.downloads'), key: 'downloads_count', align: 'end' },
  { title: t('portal.teacher.uploadedAt'), key: 'created_at' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('portal.teacher.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/teacher/study-materials/${item.id}`, { preserveScroll: true })
    },
  })
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString() : '—'
}

function goToPage(page) {
  router.get('/teacher/study-materials', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('portal.nav.studyMaterials')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.studyMaterials')"
        :subtitle="t('portal.teacher.materialsSubtitle')"
        icon="bx-folder-open"
      >
        <template #actions>
          <VBtn prepend-icon="bx-upload" href="/teacher/study-materials/create" :as="Link">
            {{ t('portal.teacher.uploadMaterial') }}
          </VBtn>
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap gap-4">
          <VTextField
            v-model="search"
            :label="t('portal.teacher.searchMaterials')"
            prepend-inner-icon="bx-search"
            density="compact"
            clearable
            hide-details
            style="max-inline-size: 320px; flex: 1 1 240px;"
          />

          <VSelect
            v-model="type"
            :items="typeOptions"
            :label="t('portal.teacher.materialType')"
            density="compact"
            clearable
            hide-details
            style="max-inline-size: 220px;"
          />
        </VCardText>

        <VDataTable
          :headers="headers"
          :items="resources.data"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.type_label="{ item }">
            <VChip size="small" color="primary" variant="tonal">
              {{ item.type_label ?? '—' }}
            </VChip>
          </template>

          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #item.actions="{ item }">
            <IconBtn v-if="item.has_file" :href="`/teacher/study-materials/${item.id}/download`">
              <VIcon icon="bx-download" />
            </IconBtn>
            <IconBtn :href="`/teacher/study-materials/${item.id}/edit`" :as="Link">
              <VIcon icon="bx-edit" />
            </IconBtn>
            <IconBtn @click="archive(item)">
              <VIcon icon="bx-archive" />
            </IconBtn>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-folder-open"
          :title="t('portal.teacher.noMaterials')"
        />
      </template>
        </VDataTable>

    <AppPagination
      :meta="resources.meta"
      @change="goToPage"
    />
      </VCard>
    </VCol>
  </VRow>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
