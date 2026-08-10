<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  guardians: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.is_active ?? null)

// Omit the key entirely when cleared, rather than sending an empty string:
// $request->has('is_active') would still be true for '', so the backend
// would read that as an explicit "false" instead of "no filter".
function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (status.value !== null)
    query.is_active = status.value

  return query
}

function applyFilters() {
  router.get('/user-management/guardians', buildQuery(), {
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

watch(status, applyFilters)

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

function goToPage(page) {
  router.get('/user-management/guardians', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
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
        v-model="search"
        :label="t('guardians.search')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <VSelect
        v-model="status"
        :label="t('common.filter')"
        :items="[
          { title: t('common.all'), value: null },
          { title: t('guardians.active'), value: true },
          { title: t('guardians.inactive'), value: false },
        ]"
        clearable
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
