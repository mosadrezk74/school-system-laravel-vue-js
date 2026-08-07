<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  templates: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  categories: { type: Array, default: () => [] },
  providers: { type: Array, default: () => [] },
})

const { t } = useI18n()

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
  if (!confirm(t('report.confirmDelete')))
    return

  router.delete(`/reports/templates/${item.id}`, { preserveScroll: true })
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('report.templates.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('report.templates.subtitle') }}</VCardSubtitle>

      <template #append>
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
    </VCardItem>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('report.searchTemplates')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 320px; flex: 1 1 240px;"
      />

      <VSelect
        v-model="category"
        :label="t('report.fields.category')"
        :items="categoryOptions"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <VSelect
        v-model="isActive"
        :label="t('report.fields.is_active')"
        :items="statusOptions"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="templates.data"
      :items-per-page="-1"
      hide-default-footer
    >
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

      <template #no-data>
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-file" size="40" class="mb-2" />
          {{ t('report.templates.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="templates.meta && templates.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="templates.meta.current_page"
          :length="templates.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
