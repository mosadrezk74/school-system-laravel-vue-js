<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  feeCategories: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()
const { formatDate } = useMoney()

const search = ref(props.filters.search ?? '')
const isActive = ref(props.filters.is_active ?? null)

const statusOptions = computed(() => [
  { title: t('finance.active'), value: '1' },
  { title: t('finance.inactive'), value: '0' },
])

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (isActive.value !== null && isActive.value !== undefined)
    query.is_active = isActive.value

  return query
}

function applyFilters() {
  router.get('/finance/fee-categories', buildQuery(), {
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

watch(isActive, applyFilters)

const headers = computed(() => [
  { title: t('finance.fields.name'), key: 'name' },
  { title: t('finance.fields.code'), key: 'code' },
  { title: t('finance.fields.description'), key: 'description' },
  { title: t('finance.fields.is_active'), key: 'is_active' },
  { title: t('finance.createdAt'), key: 'created_at' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('finance.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/finance/fee-categories/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/finance/fee-categories', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('finance.feeCategories.title')" />

  <AppPageHeader
    :title="t('finance.feeCategories.title')"
    :subtitle="t('finance.feeCategories.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/finance/fee-categories/create"
        :as="Link"
      >
        {{ t('finance.feeCategories.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="feeCategories.data"
    mobile-title-key="name"
    :meta="feeCategories.meta"
    empty-icon="bx-purchase-tag"
    :empty-title="t('finance.feeCategories.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="search"
        maxlength="100"
        :label="t('finance.searchByName')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <VSelect
        v-model="isActive"
        :label="t('finance.fields.is_active')"
        :items="statusOptions"
        clearable
      />
    </template>

      <template #item.name="{ item }">
        <AppTableLink :href="`/finance/fee-categories/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.code="{ item }">
        {{ item.code ?? '—' }}
      </template>

      <template #item.description="{ item }">
        {{ item.description ?? '—' }}
      </template>

      <template #item.is_active="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
          variant="tonal"
        >
          {{ item.is_active ? t('finance.active') : t('finance.inactive') }}
        </VChip>
      </template>

      <template #item.created_at="{ item }">
        {{ formatDate(item.created_at) }}
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/finance/fee-categories/${item.id}/edit`" :as="Link">
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
