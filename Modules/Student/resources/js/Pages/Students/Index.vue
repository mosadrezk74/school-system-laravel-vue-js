<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import { useIndexFilters } from '@/composables/useIndexFilters'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  students: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by StudentQuery::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

/*
 * Every filter this page offers — including which ones exist at all — comes
 * from the server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/user-management/students',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})

const headers = computed(() => [
  { title: t('students.name'), key: 'name' },
  { title: t('students.email'), key: 'email' },
  { title: t('students.currentClass'), key: 'current_class' },
  { title: t('students.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('students.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/user-management/students/${item.id}`, { preserveScroll: true })
    },
  })
}
</script>

<template>
  <Head :title="t('students.title')" />

  <AppPageHeader
    :title="t('students.title')"
    :subtitle="t('students.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :to="'/user-management/students/create'"
        :href="'/user-management/students/create'"
        :as="Link"
      >
        {{ t('students.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="students.data"
    mobile-title-key="name"
    :meta="students.meta"
    empty-icon="bx-user"
    :empty-title="t('students.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('students.search')"
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
        <AppTableLink :href="`/user-management/students/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.academic_year="{ item }">
        {{ item.academic_year?.name }}
      </template>

      <template #item.current_class="{ item }">
        {{ item.current_class ? item.current_class.name : '—' }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_active ? t('students.active') : t('students.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/user-management/students/${item.id}/edit`" :as="Link">
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
