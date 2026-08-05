<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  guardians: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

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
  if (!confirm(t('guardians.confirmArchive')))
    return

  router.delete(`/user-management/guardians/${item.id}`, { preserveScroll: true })
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('guardians.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('guardians.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :to="'/user-management/guardians/create'"
          :href="'/user-management/guardians/create'"
          :as="Link"
        >
          {{ t('guardians.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('guardians.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 360px; flex: 1 1 260px;"
      />

      <VSelect
        v-model="status"
        :label="t('common.filter')"
        :items="[
          { title: t('common.all'), value: null },
          { title: t('guardians.active'), value: true },
          { title: t('guardians.inactive'), value: false },
        ]"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="guardians.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.status="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_active ? t('guardians.active') : t('guardians.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/user-management/guardians/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/user-management/guardians/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-user" size="40" class="mb-2" />
          {{ t('guardians.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="guardians.meta && guardians.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="guardians.meta.current_page"
          :length="guardians.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
