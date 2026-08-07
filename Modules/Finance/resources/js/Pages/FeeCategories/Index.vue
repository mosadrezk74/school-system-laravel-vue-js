<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'

const props = defineProps({
  feeCategories: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
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
  if (!confirm(t('finance.confirmArchive')))
    return

  router.delete(`/finance/fee-categories/${item.id}`, { preserveScroll: true })
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.feeCategories.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.feeCategories.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          href="/finance/fee-categories/create"
          :as="Link"
        >
          {{ t('finance.feeCategories.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('finance.searchByName')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 320px; flex: 1 1 240px;"
      />

      <VSelect
        v-model="isActive"
        :label="t('finance.fields.is_active')"
        :items="statusOptions"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="feeCategories.data"
      :items-per-page="-1"
      hide-default-footer
    >
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
        <IconBtn :href="`/finance/fee-categories/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/finance/fee-categories/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-purchase-tag" size="40" class="mb-2" />
          {{ t('finance.feeCategories.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="feeCategories.meta && feeCategories.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="feeCategories.meta.current_page"
          :length="feeCategories.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
