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
  classes: { type: Object, required: true },
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
  url: '/academic/classes',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})





const headers = computed(() => [
  { title: t('classes.name'), key: 'name' },
  { title: t('classes.gradeLevel'), key: 'grade_level' },
  { title: t('classes.capacity'), key: 'capacity', sortable: false },
  { title: t('classes.active'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('classes.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/academic/classes/${item.id}`, { preserveScroll: true })
    },
  })
}

</script>

<template>
  <Head :title="t('classes.title')" />

  <AppPageHeader
    :title="t('classes.title')"
    :subtitle="t('classes.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :to="'/academic/classes/create'"
        :href="'/academic/classes/create'"
        :as="Link"
      >
        {{ t('classes.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="classes.data"
    mobile-title-key="name"
    :meta="classes.meta"
    empty-icon="bx-chalkboard"
    :empty-title="t('classes.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('classes.search')"
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
        <AppTableLink :href="`/academic/classes/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>


      <template #item.grade_level="{ item }">
        {{ item.grade_level?.name }}
      </template>

      <template #item.capacity="{ item }">
        {{ item.capacity }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_active ? t('classes.active') : t('classes.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/academic/classes/${item.id}/edit`" :as="Link">
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