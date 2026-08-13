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
  news: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
  statusOptions: { type: Array, default: () => [] },
  audienceOptions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/communication/news',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})

const headers = computed(() => [
  { title: t('news.fields.title'), key: 'title', sortable: false },
  { title: t('news.fields.audience'), key: 'audience', sortable: false },
  { title: t('news.fields.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const withAll = options => [{ title: t('common.all'), value: null }, ...options]

const pending = ref(null)

const askDelete = item => {
  pending.value = {
    message: t('news.confirmDelete', { title: item.title }),
    confirmText: t('common.delete'),
    color: 'error',
    run: () => router.delete(`/communication/news/${item.id}`, { preserveScroll: true }),
  }
}

const isConfirmOpen = computed({
  get: () => pending.value !== null,
  set: value => { if (!value) pending.value = null },
})

const togglePublish = item => {
  const url = item.status === 'published'
    ? `/communication/news/${item.id}/unpublish`
    : `/communication/news/${item.id}/publish`

  router.patch(url, {}, { preserveScroll: true })
}

const togglePin = item => {
  router.patch(
    `/communication/news/${item.id}/pin`,
    { is_pinned: !item.is_pinned },
    { preserveScroll: true },
  )
}

const formatDate = value => (value ? new Date(value).toLocaleString() : '—')
</script>

<template>
  <Head :title="t('news.title')" />

  <AppPageHeader
    :title="t('news.title')"
    :subtitle="t('news.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/communication/news/create"
        :as="Link"
        color="primary"
      >
        {{ t('news.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="news.data"
    :meta="news.meta"
    mobile-title-key="title"
    :empty-title="t('news.empty')"
    empty-icon="bx-news"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('news.search')"
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

    <template #item.title="{ item }">
      <div class="d-flex align-center gap-2">
        <VIcon
          v-if="item.is_pinned"
          icon="bx-pin"
          size="16"
          color="primary"
        />
        <AppTableLink :href="`/communication/news/${item.id}/edit`">
          {{ item.title || '—' }}
        </AppTableLink>
      </div>
    </template>

    <template #item.audience="{ item }">
      <VChip size="small">
        {{ item.audience_label }}
      </VChip>
    </template>

    <template #item.status="{ item }">
      <VChip
        :color="item.status_color"
        size="small"
      >
        {{ item.status_label }}
      </VChip>
      <VChip
        v-if="item.is_scheduled"
        size="x-small"
        color="info"
        class="ms-1"
      >
        {{ t('news.scheduled') }}
      </VChip>
    </template>

    <template #item.published_at="{ item }">
      {{ formatDate(item.published_at) }}
    </template>

    <template #item.author="{ item }">
      {{ item.author || '—' }}
    </template>

    <template #item.actions="{ item }">
      <IconBtn
        :href="`/communication/news/${item.id}/edit`"
        :as="Link"
      >
        <VIcon icon="bx-edit" />
      </IconBtn>
      <IconBtn @click="togglePublish(item)">
        <VIcon :icon="item.status === 'published' ? 'bx-hide' : 'bx-upload'" />
      </IconBtn>
      <IconBtn @click="togglePin(item)">
        <VIcon :icon="item.is_pinned ? 'bx-pin' : 'bxs-pin'" />
      </IconBtn>
      <IconBtn @click="askDelete(item)">
        <VIcon icon="bx-trash" />
      </IconBtn>
    </template>

    <template #empty-action>
      <VBtn
        prepend-icon="bx-plus"
        href="/communication/news/create"
        :as="Link"
        color="primary"
      >
        {{ t('news.addNew') }}
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
