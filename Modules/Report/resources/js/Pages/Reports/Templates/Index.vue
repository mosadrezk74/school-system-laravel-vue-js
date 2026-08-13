<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'

const props = defineProps({
  templates: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  categories: { type: Array, default: () => [] },
  providers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const category = ref(props.filters.category ?? null)
const isActive = ref(props.filters.is_active ?? null)

const categoryOptions = computed(() => props.categories.map(item => ({
  title: item.label,
  value: item.value,
})))

const statusOptions = computed(() => [
  { title: t('report.options.active'), value: '1' },
  { title: t('report.options.inactive'), value: '0' },
])

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (category.value)
    query.category = category.value

  if (isActive.value !== null && isActive.value !== undefined)
    query.is_active = isActive.value

  return query
}

function applyFilters() {
  router.get('/reports/templates', buildQuery(), {
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

watch([category, isActive], applyFilters)

const headers = computed(() => [
  { title: t('report.fields.name'), key: 'name' },
  { title: t('report.fields.code'), key: 'code' },
  { title: t('report.fields.category'), key: 'category' },
  { title: t('report.fields.is_active'), key: 'is_active' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function categoryLabel(value) {
  return props.categories.find(item => item.value === value)?.label ?? '—'
}

function remove(item) {
  askConfirm({
    message: t('report.confirmDelete'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/reports/templates/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/reports/templates', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('report.templates.title')" />

  <AppPageHeader
    :title="t('report.templates.title')"
    :subtitle="t('report.templates.subtitle')"
  >
    <template #actions>
      <div class="d-flex gap-3">
        <VBtn
          variant="tonal"
          color="secondary"
          prepend-icon="bx-arrow-back"
          href="/reports"
          :as="Link"
        >
          {{ t('report.backToReports') }}
        </VBtn>
        <VBtn
          prepend-icon="bx-plus"
          href="/reports/templates/create"
          :as="Link"
        >
          {{ t('report.templates.addNew') }}
        </VBtn>
      </div>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="templates.data"
    :meta="templates.meta"
    empty-icon="bx-file"
    :empty-title="t('report.templates.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="search"
        maxlength="100"
        :label="t('report.searchTemplates')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <VSelect
        v-model="category"
        :label="t('report.fields.category')"
        :items="categoryOptions"
        clearable
      />

      <VSelect
        v-model="isActive"
        :label="t('report.fields.is_active')"
        :items="statusOptions"
        clearable
      />
    </template>

      <template #item.code="{ item }">
        {{ item.code ?? '—' }}
      </template>

      <template #item.category="{ item }">
        <VChip size="small" variant="tonal">
          {{ categoryLabel(item.category) }}
        </VChip>
      </template>

      <template #item.is_active="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
          variant="tonal"
        >
          {{ item.is_active ? t('report.options.active') : t('report.options.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/reports/templates/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="remove(item)">
          <VIcon icon="bx-trash" />
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
