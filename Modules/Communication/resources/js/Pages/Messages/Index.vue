<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  messages: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? null)

function applyFilters() {
  router.get('/communication/messages', { search: search.value, status: status.value }, {
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

function goToPage(page) {
  router.get('/communication/messages', { search: search.value, status: status.value, page }, {
    preserveState: true,
    preserveScroll: true,
  })
}
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
        v-model="search"
        :label="t('communication.messages.search')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <VSelect
        v-model="status"
        :label="t('communication.messages.status')"
        :items="[
          { title: t('communication.messages.statuses.pending'), value: 'pending' },
          { title: t('communication.messages.statuses.sent'), value: 'sent' },
        ]"
        clearable
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
