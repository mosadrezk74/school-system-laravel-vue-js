<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  guardians: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/user-management/guardians',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})






const headers = computed(() => [
  { title: t('guardians.name'), key: 'name' },
  { title: t('guardians.email'), key: 'email' },
  { title: t('guardians.phone'), key: 'phone' },
  { title: t('guardians.occupation'), key: 'occupation' },
  { title: t('guardians.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('guardians.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/user-management/guardians/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('guardians.title')" />

  <AppPageHeader
    :title="t('guardians.title')"
    :subtitle="t('guardians.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :to="'/user-management/guardians/create'"
        :href="'/user-management/guardians/create'"
        :as="Link"
      >
        {{ t('guardians.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="guardians.data"
    mobile-title-key="name"
    :meta="guardians.meta"
    empty-icon="bx-user"
    :empty-title="t('guardians.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('guardians.search')"
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
        <AppTableLink :href="`/user-management/guardians/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_active ? t('guardians.active') : t('guardians.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/user-management/guardians/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>
  </AppDataTable>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
