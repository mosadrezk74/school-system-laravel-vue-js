<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppDataTable from '@/components/AppDataTable.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  admins: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  currentUserId: { type: Number, default: null },
})

const { t } = useI18n()

const { filters, goToPage } = useIndexFilters({
  url: '/access-control/admins',
  initial: {
    search: props.filters.search ?? null,
    is_active: props.filters.is_active ?? null,
    account_role: props.filters.account_role ?? null,
  },
})

const headers = computed(() => [
  { title: t('admins.fields.name'), key: 'name' },
  { title: t('admins.fields.email'), key: 'email', sortable: false },
  { title: t('admins.fields.account_role'), key: 'account_role', sortable: false },
  { title: t('admins.fields.roles'), key: 'roles', sortable: false },
  { title: t('admins.fields.is_active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const activeOptions = computed(() => [
  { title: t('common.all'), value: null },
  { title: t('common.active'), value: true },
  { title: t('common.inactive'), value: false },
])

const accountRoleOptions = computed(() => [
  { title: t('common.all'), value: null },
  { title: t('admins.owner'), value: 'owner' },
  { title: t('admins.admin'), value: 'admin' },
])

const pending = ref(null)

const askDelete = item => {
  pending.value = {
    message: t('admins.confirmDelete', { name: item.name }),
    confirmText: t('common.delete'),
    color: 'error',
    run: () => router.delete(`/access-control/admins/${item.id}`, { preserveScroll: true }),
  }
}

const askToggle = item => {
  const next = !item.is_active

  pending.value = {
    message: next
      ? t('admins.confirmActivate', { name: item.name })
      : t('admins.confirmDeactivate', { name: item.name }),
    confirmText: next ? t('admins.activate') : t('admins.deactivate'),
    color: next ? 'primary' : 'warning',
    run: () => router.patch(
      `/access-control/admins/${item.id}/activation`,
      { is_active: next },
      { preserveScroll: true },
    ),
  }
}

const isConfirmOpen = computed({
  get: () => pending.value !== null,
  set: value => { if (!value) pending.value = null },
})

/*
 * Hiding these controls only avoids offering an action the server would
 * refuse — AdminGuardService is what actually prevents self-deletion and
 * removing the last administrator.
 */
const isSelf = item => item.id === props.currentUserId
</script>

<template>
  <Head :title="t('admins.title')" />

  <AppPageHeader
    :title="t('admins.title')"
    :subtitle="t('admins.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/access-control/admins/create"
        :as="Link"
        color="primary"
      >
        {{ t('admins.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="admins.data"
    :meta="admins.meta"
    mobile-title-key="name"
    :empty-title="t('admins.empty')"
    empty-icon="bx-user-circle"
    @page="goToPage"
  >
    <template #filters>
      <VRow dense>
        <VCol
          cols="12"
          md="5"
        >
          <VTextField
            v-model="filters.search.value"
            :label="t('admins.search')"
            prepend-inner-icon="bx-search"
            clearable
          />
        </VCol>

        <VCol
          cols="12"
          md="3"
        >
          <VSelect
            v-model="filters.account_role.value"
            :items="accountRoleOptions"
            :label="t('admins.fields.account_role')"
          />
        </VCol>

        <VCol
          cols="12"
          md="3"
        >
          <VSelect
            v-model="filters.is_active.value"
            :items="activeOptions"
            :label="t('admins.fields.is_active')"
          />
        </VCol>
      </VRow>
    </template>

    <template #item.name="{ item }">
      <AppTableLink :href="`/access-control/admins/${item.id}/edit`">
        {{ item.name }}
      </AppTableLink>
      <VChip
        v-if="isSelf(item)"
        size="x-small"
        class="ms-2"
      >
        {{ t('admins.you') }}
      </VChip>
    </template>

    <template #item.account_role="{ item }">
      <VChip
        :color="item.account_role === 'owner' ? 'warning' : 'primary'"
        size="small"
      >
        {{ item.account_role_label }}
      </VChip>
    </template>

    <template #item.roles="{ item }">
      <span v-if="!item.roles?.length">—</span>
      <VChip
        v-for="role in item.roles"
        v-else
        :key="role"
        size="x-small"
        class="me-1"
      >
        {{ role }}
      </VChip>
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
        :href="`/access-control/admins/${item.id}/edit`"
        :as="Link"
      >
        <VIcon icon="bx-edit" />
      </IconBtn>
      <IconBtn
        v-if="!isSelf(item)"
        @click="askToggle(item)"
      >
        <VIcon :icon="item.is_active ? 'bx-block' : 'bx-check-circle'" />
      </IconBtn>
      <IconBtn
        v-if="!isSelf(item)"
        @click="askDelete(item)"
      >
        <VIcon icon="bx-trash" />
      </IconBtn>
    </template>

    <template #empty-action>
      <VBtn
        prepend-icon="bx-plus"
        href="/access-control/admins/create"
        :as="Link"
        color="primary"
      >
        {{ t('admins.addNew') }}
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
