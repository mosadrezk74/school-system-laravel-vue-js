<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

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
  { title: '', key: 'actions', sortable: false, align: 'end' },
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('communication.messages.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('communication.messages.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :href="'/communication/messages/create'"
          :as="Link"
        >
          {{ t('communication.messages.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('communication.messages.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 320px; flex: 1 1 240px;"
      />

      <VSelect
        v-model="status"
        :label="t('communication.messages.status')"
        :items="[
          { title: t('communication.messages.statuses.pending'), value: 'pending' },
          { title: t('communication.messages.statuses.sent'), value: 'sent' },
        ]"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="messages.data"
      :items-per-page="-1"
      hide-default-footer
    >
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

      <template #item.actions="{ item }">
        <IconBtn :href="`/communication/messages/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
      </template>

      <template #no-data>
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-message-detail" size="40" class="mb-2" />
          {{ t('communication.messages.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="messages.meta && messages.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="messages.meta.current_page"
          :length="messages.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
