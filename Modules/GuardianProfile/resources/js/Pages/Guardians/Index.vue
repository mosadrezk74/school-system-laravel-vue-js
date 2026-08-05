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

let searchTimer
watch(search, value => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/user-management/guardians', { search: value }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 350)
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
  if (!confirm(t('guardians.confirmArchive')))
    return

  router.delete(`/user-management/guardians/${item.id}`, { preserveScroll: true })
}

function goToPage(page) {
  router.get('/user-management/guardians', { page, search: search.value }, {
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

    <VCardText>
      <VTextField
        v-model="search"
        :label="t('guardians.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 360px;"
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
        <div class="pa-8 text-center text-disabled">
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
