<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  messages: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
})

const { t } = useI18n()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/communication/messages',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})





const headers = computed(() => [
  { title: t('communication.fields.title'), key: 'title' },
  { title: t('communication.fields.targetType'), key: 'target_type' },
  { title: t('communication.messages.status'), key: 'status', sortable: false },
  { title: t('communication.messages.sentAt'), key: 'sent_at' },
  /*
   * No actions column: a broadcast message has no edit or delete route, so
   * View was its only action — and the title now carries that navigation.
   * An empty column would just be dead space at the end of every row.
   */
])

</script>

<template>
  <Head :title="t('communication.messages.title')" />

  <AppPageHeader
    :title="t('communication.messages.title')"
    :subtitle="t('communication.messages.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :href="'/communication/messages/create'"
        :as="Link"
      >
        {{ t('communication.messages.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="messages.data"
    mobile-title-key="title"
    :meta="messages.meta"
    empty-icon="bx-message-detail"
    :empty-title="t('communication.messages.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('communication.messages.search')"
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
        <AppTableLink :href="`/communication/messages/${item.id}`">
          {{ item.title ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.target_type="{ item }">
        {{ t(`communication.audience.${item.target_type}`) }}
      </template>

      <template #item.status="{ item }">
        <VChip :color="item.status === 'sent' ? 'success' : 'secondary'" size="small">
          {{ t(`communication.messages.statuses.${item.status}`) }}
        </VChip>
      </template>

      <template #item.sent_at="{ item }">
        {{ item.sent_at ? new Date(item.sent_at).toLocaleString() : '—' }}
      </template>

  </AppDataTable>
</template>
