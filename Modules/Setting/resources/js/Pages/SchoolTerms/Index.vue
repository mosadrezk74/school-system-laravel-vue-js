<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppDataTable from '@/components/AppDataTable.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  terms: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  statusOptions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { filters, goToPage } = useIndexFilters({
  url: '/settings/school-terms',
  initial: {
    search: props.filters.search ?? null,
    status: props.filters.status ?? null,
  },
})

const headers = computed(() => [
  { title: t('schoolTerms.fields.version'), key: 'version' },
  { title: t('schoolTerms.fields.title'), key: 'title', sortable: false },
  { title: t('schoolTerms.fields.status'), key: 'status', sortable: false },
  { title: t('schoolTerms.fields.publishedAt'), key: 'published_at', sortable: false },
  { title: t('schoolTerms.fields.acceptances'), key: 'acceptances_count', sortable: false, align: 'end' },
  { title: t('schoolTerms.fields.createdBy'), key: 'created_by', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const statusFilterOptions = computed(() => [
  { title: t('common.all'), value: null },
  ...props.statusOptions.map(option => ({
    title: t(`schoolTerms.statuses.${option.value}`),
    value: option.value,
  })),
])

const pending = ref(null)

const askPublish = item => {
  pending.value = {
    message: t('schoolTerms.confirmPublish', { version: item.version }),
    confirmText: t('schoolTerms.publish'),
    color: 'primary',
    run: () => router.patch(`/settings/school-terms/${item.id}/publish`, {}, { preserveScroll: true }),
  }
}

const askDelete = item => {
  pending.value = {
    message: t('schoolTerms.confirmDelete', { version: item.version }),
    confirmText: t('common.delete'),
    color: 'error',
    run: () => router.delete(`/settings/school-terms/${item.id}`, { preserveScroll: true }),
  }
}

const isConfirmOpen = computed({
  get: () => pending.value !== null,
  set: value => { if (!value) pending.value = null },
})

const formatDate = value => (value ? new Date(value).toLocaleDateString() : '—')

/*
 * A published version's wording is frozen, so only a draft offers edit,
 * publish and delete. The policy is what actually enforces this.
 */
const isDraft = item => item.status === 'draft'
</script>

<template>
  <Head :title="t('schoolTerms.title')" />

  <AppPageHeader
    :title="t('schoolTerms.title')"
    :subtitle="t('schoolTerms.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/settings/school-terms/create"
        :as="Link"
        color="primary"
      >
        {{ t('schoolTerms.newVersion') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="terms.data"
    :meta="terms.meta"
    mobile-title-key="title"
    :empty-title="t('schoolTerms.empty')"
    empty-icon="bx-file"
    @page="goToPage"
  >
    <template #filters>
      <VRow dense>
        <VCol
          cols="12"
          md="6"
        >
          <VTextField
            v-model="filters.search.value"
            maxlength="100"
            :label="t('schoolTerms.search')"
            prepend-inner-icon="bx-search"
            clearable
          />
        </VCol>

        <VCol
          cols="12"
          md="4"
        >
          <VSelect
            v-model="filters.status.value"
            :items="statusFilterOptions"
            :label="t('schoolTerms.fields.status')"
          />
        </VCol>
      </VRow>
    </template>

    <template #item.version="{ item }">
      <span class="font-weight-medium">v{{ item.version }}</span>
    </template>

    <template #item.title="{ item }">
      <AppTableLink :href="`/settings/school-terms/${item.id}`">
        {{ item.title || '—' }}
      </AppTableLink>
    </template>

    <template #item.status="{ item }">
      <VChip
        :color="item.status_color"
        size="small"
      >
        {{ t(`schoolTerms.statuses.${item.status}`) }}
      </VChip>
    </template>

    <template #item.published_at="{ item }">
      {{ formatDate(item.published_at) }}
    </template>

    <template #item.acceptances_count="{ item }">
      {{ item.acceptances_count ?? 0 }}
    </template>

    <template #item.created_by="{ item }">
      {{ item.created_by || '—' }}
    </template>

    <template #item.actions="{ item }">
      <IconBtn
        :href="`/settings/school-terms/${item.id}`"
        :as="Link"
      >
        <VIcon icon="bx-show" />
      </IconBtn>

      <template v-if="isDraft(item)">
        <IconBtn
          :href="`/settings/school-terms/${item.id}/edit`"
          :as="Link"
        >
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="askPublish(item)">
          <VIcon icon="bx-upload" />
        </IconBtn>
        <IconBtn @click="askDelete(item)">
          <VIcon icon="bx-trash" />
        </IconBtn>
      </template>
    </template>

    <template #empty-action>
      <VBtn
        prepend-icon="bx-plus"
        href="/settings/school-terms/create"
        :as="Link"
        color="primary"
      >
        {{ t('schoolTerms.newVersion') }}
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
