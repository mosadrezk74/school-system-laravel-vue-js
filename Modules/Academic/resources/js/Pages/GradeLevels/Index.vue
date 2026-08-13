<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  gradeLevels: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/academic/grade-levels',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})

const headers = computed(() => [
  { title: t('gradeLevels.fields.displayOrder'), key: 'display_order' },
  { title: t('gradeLevels.fields.name'), key: 'name', sortable: false },
  { title: t('gradeLevels.fields.description'), key: 'description', sortable: false },
  { title: t('gradeLevels.fields.isActive'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const activeOptions = computed(() => [
  { title: t('common.all'), value: null },
  { title: t('common.yes'), value: true },
  { title: t('common.no'), value: false },
])

/*
 * Archive and restore share one dialog: the pending row and the action to run
 * are captured together, so the confirm button never has to work out which of
 * the two it is committing to.
 */
const pending = ref(null)

const askArchive = item => {
  pending.value = {
    item,
    message: t('gradeLevels.confirmArchive'),
    confirmText: t('common.archive'),
    color: 'error',
    run: () => router.delete(`/academic/grade-levels/${item.id}`, { preserveScroll: true }),
  }
}

const askRestore = item => {
  pending.value = {
    item,
    message: t('common.confirmRestore'),
    confirmText: t('common.restore'),
    color: 'primary',
    run: () => router.patch(`/academic/grade-levels/${item.id}/restore`, {}, { preserveScroll: true }),
  }
}

const isConfirmOpen = computed({
  get: () => pending.value !== null,
  set: value => { if (!value) pending.value = null },
})
</script>

<template>
  <Head :title="t('gradeLevels.title')" />

  <AppPageHeader
    :title="t('gradeLevels.title')"
    :subtitle="t('gradeLevels.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/academic/grade-levels/create"
        :as="Link"
        color="primary"
      >
        {{ t('gradeLevels.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="gradeLevels.data"
    :meta="gradeLevels.meta"
    mobile-title-key="name"
    :empty-title="t('gradeLevels.empty')"
    empty-icon="bx-layer"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('gradeLevels.search')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <AppFilterBar
        :definitions="filterDefinitions"
        :filters="filters"
        :active-keys="activeKeys"
        @clear="clear"
        @clear-all="reset"
      />
    </template>

    <template #item.name="{ item }">
      <div class="d-flex align-center gap-2">
        <AppTableLink :href="`/academic/grade-levels/${item.id}`">
          {{ item.name || '—' }}
        </AppTableLink>
        <VChip
          v-if="item.deleted_at"
          size="x-small"
          color="warning"
        >
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
      <IconBtn
        :href="`/academic/grade-levels/${item.id}/edit`"
        :as="Link"
      >
        <VIcon icon="bx-edit" />
      </IconBtn>
      <IconBtn
        v-if="!item.deleted_at"
        @click="askArchive(item)"
      >
        <VIcon icon="bx-archive" />
      </IconBtn>
      <IconBtn
        v-else
        @click="askRestore(item)"
      >
        <VIcon icon="bx-refresh" />
      </IconBtn>
    </template>

    <template #empty-action>
      <VBtn
        prepend-icon="bx-plus"
        href="/academic/grade-levels/create"
        :as="Link"
        color="primary"
      >
        {{ t('gradeLevels.addNew') }}
      </VBtn>
    </template>
  </AppDataTable>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :message="pending?.message"
    :confirm-text="pending?.confirmText"
    :color="pending?.color ?? 'error'"
    @confirm="pending?.run()"
  />
</template>
