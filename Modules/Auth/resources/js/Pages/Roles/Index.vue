<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  roles: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/access-control/roles',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})

const headers = computed(() => [
  { title: t('roles.fields.name'), key: 'name' },
  { title: t('roles.fields.is_system'), key: 'is_system', sortable: false },
  { title: t('roles.fields.permissions_count'), key: 'permissions_count', sortable: false, align: 'end' },
  { title: t('roles.fields.users_count'), key: 'users_count', sortable: false, align: 'end' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const systemOptions = computed(() => [
  { title: t('common.all'), value: null },
  { title: t('roles.filterSystem'), value: true },
  { title: t('roles.filterCustom'), value: false },
])

const pending = ref(null)

/*
 * The server refuses a system role or one that is still assigned; the dialog
 * only avoids offering an action that is certain to fail.
 */
const askDelete = item => {
  pending.value = {
    message: t('roles.confirmDelete', { name: item.label }),
    run: () => router.delete(`/access-control/roles/${item.id}`, { preserveScroll: true }),
  }
}

const isConfirmOpen = computed({
  get: () => pending.value !== null,
  set: value => { if (!value) pending.value = null },
})

const canDelete = item => !item.is_system && item.users_count === 0
</script>

<template>
  <Head :title="t('roles.title')" />

  <AppPageHeader
    :title="t('roles.title')"
    :subtitle="t('roles.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/access-control/roles/create"
        :as="Link"
        color="primary"
      >
        {{ t('roles.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="roles.data"
    :meta="roles.meta"
    mobile-title-key="name"
    :empty-title="t('roles.empty')"
    empty-icon="bx-shield-quarter"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('roles.search')"
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
      <AppTableLink :href="`/access-control/roles/${item.id}/edit`">
        {{ item.label }}
      </AppTableLink>
      <div class="text-caption text-medium-emphasis">
        {{ item.name }}
      </div>
    </template>

    <template #item.is_system="{ item }">
      <VChip
        :color="item.is_system ? 'primary' : 'secondary'"
        size="small"
      >
        {{ item.is_system ? t('roles.filterSystem') : t('roles.filterCustom') }}
      </VChip>
    </template>

    <template #item.permissions_count="{ item }">
      {{ item.permissions_count ?? 0 }}
    </template>

    <template #item.users_count="{ item }">
      {{ item.users_count ?? 0 }}
    </template>

    <template #item.actions="{ item }">
      <IconBtn
        :href="`/access-control/roles/${item.id}/edit`"
        :as="Link"
      >
        <VIcon icon="bx-edit" />
      </IconBtn>
      <IconBtn
        v-if="canDelete(item)"
        @click="askDelete(item)"
      >
        <VIcon icon="bx-trash" />
      </IconBtn>
    </template>

    <template #empty-action>
      <VBtn
        prepend-icon="bx-plus"
        href="/access-control/roles/create"
        :as="Link"
        color="primary"
      >
        {{ t('roles.addNew') }}
      </VBtn>
    </template>
  </AppDataTable>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :message="pending?.message"
    :confirm-text="t('common.delete')"
    color="error"
    @confirm="pending?.run()"
  />
</template>
