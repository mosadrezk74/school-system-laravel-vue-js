<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  branches: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')
const isActiveFilter = ref(props.filters.is_active ?? null)
const isMainFilter = ref(props.filters.is_main ?? null)
const cityFilter = ref(props.filters.city ?? '')

let searchTimer
watch(search, value => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    applyFilters()
  }, 350)
})

watch([isActiveFilter, isMainFilter, cityFilter], () => {
  applyFilters()
})

function applyFilters() {
  const params = {
    search: search.value || undefined,
    is_active: isActiveFilter.value !== null ? isActiveFilter.value : undefined,
    is_main: isMainFilter.value !== null ? isMainFilter.value : undefined,
    city: cityFilter.value || undefined,
  }

  router.get('/settings/branches', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

const headers = computed(() => [
  { title: t('branch.fields.name'), key: 'name' },
  { title: t('branch.fields.code'), key: 'code' },
  { title: t('branch.fields.city'), key: 'city' },
  { title: t('branch.fields.phone'), key: 'phone' },
  { title: t('branch.fields.is_main'), key: 'is_main', sortable: false },
  { title: t('branch.fields.is_active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  if (!confirm(t('common.confirmArchive')))
    return

  router.delete(`/settings/branches/${item.id}`, { preserveScroll: true })
}

function restore(item) {
  if (!confirm(t('common.confirmRestore')))
    return

  router.patch(`/settings/branches/${item.id}/restore`, {}, { preserveScroll: true })
}

function goToPage(page) {
  const params = {
    page,
    search: search.value || undefined,
    is_active: isActiveFilter.value !== null ? isActiveFilter.value : undefined,
    is_main: isMainFilter.value !== null ? isMainFilter.value : undefined,
    city: cityFilter.value || undefined,
  }

  router.get('/settings/branches', params, {
    preserveState: true,
    preserveScroll: true,
  })
}

const filterOptions = {
  active: [
    { title: t('common.all'), value: null },
    { title: t('common.yes'), value: true },
    { title: t('common.no'), value: false },
  ],
  main: [
    { title: t('common.all'), value: null },
    { title: t('common.yes'), value: true },
    { title: t('common.no'), value: false },
  ],
}
</script>

<template>
  <Head :title="t('branch.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('branch.title') }}</VCardTitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :to="'/settings/branches/create'"
          :href="'/settings/branches/create'"
          :as="Link"
          color="primary"
        >
          {{ t('branch.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VCardText>
      <VRow>
        <VCol cols="12" md="4">
          <VTextField
            v-model="search"
            :label="t('common.search')"
            prepend-inner-icon="bx-search"
            clearable
            density="compact"
          />
        </VCol>

        <VCol cols="12" md="2">
          <VSelect
            v-model="isActiveFilter"
            :items="filterOptions.active"
            :label="t('branch.fields.is_active')"
            density="compact"
            clearable
          />
        </VCol>

        <VCol cols="12" md="2">
          <VSelect
            v-model="isMainFilter"
            :items="filterOptions.main"
            :label="t('branch.fields.is_main')"
            density="compact"
            clearable
          />
        </VCol>

        <VCol cols="12" md="3">
          <VTextField
            v-model="cityFilter"
            :label="t('branch.fields.city')"
            density="compact"
            clearable
          />
        </VCol>

        <VCol cols="12" md="1" class="d-flex align-center">
          <VBtn
            variant="tonal"
            color="secondary"
            size="small"
            @click="applyFilters"
          >
            {{ t('common.filter') }}
          </VBtn>
        </VCol>
      </VRow>
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="branches.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.is_main="{ item }">
        <VIcon
          :icon="item.is_main ? 'bx-check-circle' : 'bx-circle'"
          :color="item.is_main ? 'success' : 'grey'"
          size="20"
        />
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
        <IconBtn :href="`/settings/branches/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/settings/branches/${item.id}/edit`" :as="Link">
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
        <div class="pa-8 text-center text-disabled">
          {{ t('common.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="branches.meta && branches.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="branches.meta.current_page"
          :length="branches.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>