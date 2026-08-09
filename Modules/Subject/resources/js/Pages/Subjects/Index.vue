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
  subjects: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const isActiveFilter = ref(props.filters.is_active ?? null)

// Debounced so typing does not fire a request per keystroke.
let searchTimer
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 350)
})

watch(isActiveFilter, applyFilters)

function currentParams(extra = {}) {
  return {
    search: search.value || undefined,
    is_active: isActiveFilter.value !== null ? isActiveFilter.value : undefined,
    ...extra,
  }
}

function applyFilters() {
  router.get('/academic/subjects', currentParams(), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

function goToPage(page) {
  router.get('/academic/subjects', currentParams({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}

const headers = computed(() => [
  { title: t('subject.fields.name'), key: 'name', sortable: false },
  { title: t('subject.fields.description'), key: 'description', sortable: false },
  { title: t('subject.fields.is_active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const activeOptions = computed(() => [
  { title: t('common.all'), value: null },
  { title: t('common.yes'), value: true },
  { title: t('common.no'), value: false },
])

function archive(item) {
  askConfirm({
    message: t('subject.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/academic/subjects/${item.id}`, { preserveScroll: true })
    },
  })
}

function restore(item) {
  askConfirm({
    message: t('common.confirmRestore'),
    color: 'primary',
    onConfirm: () => {
      router.patch(`/academic/subjects/${item.id}/restore`, {}, { preserveScroll: true })
    },
  })
}
</script>

<template>
  <Head :title="t('subject.title')" />

  <AppPageHeader
    :title="t('subject.title')"
    :subtitle="t('subject.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/academic/subjects/create"
        :as="Link"
        color="primary"
      >
        {{ t('subject.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VCardText>
      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="search"
            :label="t('subject.search')"
            prepend-inner-icon="bx-search"
            clearable
            density="compact"
            hide-details
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="isActiveFilter"
            :items="activeOptions"
            :label="t('subject.fields.is_active')"
            density="compact"
            hide-details
          />
        </VCol>
      </VRow>
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="subjects.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.name="{ item }">
        <div class="d-flex align-center gap-2">
          <span>{{ item.name || '—' }}</span>
          <VChip v-if="item.deleted_at" size="x-small" color="warning">
            {{ t('common.archived') }}
          </VChip>
        </div>
      </template>

      <template #item.description="{ item }">
        {{ item.description || '—' }}
      </template>

      <template #item.is_active="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_active ? t('common.active') : t('common.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/academic/subjects/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/academic/subjects/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn v-if="!item.deleted_at" @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
        <IconBtn v-else @click="restore(item)">
          <VIcon icon="bx-refresh" />
        </IconBtn>
      </template>

      <template #no-data>
        <AppEmptyState :title="t('subject.empty')" />
      </template>
    </VDataTable>

    <AppPagination
      :meta="subjects.meta"
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
