<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  subjects: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')
const subjectTypeFilter = ref(props.filters.subject_type ?? null)
const isActiveFilter = ref(props.filters.is_active ?? null)

let searchTimer
watch(search, value => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    applyFilters()
  }, 350)
})

watch([subjectTypeFilter, isActiveFilter], () => {
  applyFilters()
})

function applyFilters() {
  const params = {
    search: search.value || undefined,
    subject_type: subjectTypeFilter.value || undefined,
    is_active: isActiveFilter.value !== null ? isActiveFilter.value : undefined,
  }

  router.get('/academic/subjects', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

const headers = computed(() => [
  { title: t('subject.fields.name'), key: 'name' },
  { title: t('subject.fields.code'), key: 'code' },
  { title: t('subject.fields.short_name'), key: 'short_name' },
  { title: t('subject.fields.subject_type'), key: 'subject_type' },
  { title: t('subject.fields.is_active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const typeLabels = {
  core: t('subject.types.core'),
  elective: t('subject.types.elective'),
  extracurricular: t('subject.types.extracurricular'),
}

function archive(item) {
  if (!confirm(t('subject.confirmArchive'))) return
  router.delete(`/academic/subjects/${item.id}`, { preserveScroll: true })
}

function restore(item) {
  if (!confirm(t('common.confirmRestore'))) return
  router.patch(`/academic/subjects/${item.id}/restore`, {}, { preserveScroll: true })
}

function goToPage(page) {
  const params = {
    page,
    search: search.value || undefined,
    subject_type: subjectTypeFilter.value || undefined,
    is_active: isActiveFilter.value !== null ? isActiveFilter.value : undefined,
  }

  router.get('/academic/subjects', params, {
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
}
</script>

<template>
  <Head :title="t('subject.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('subject.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('subject.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :href="'/academic/subjects/create'"
          :as="Link"
          color="primary"
        >
          {{ t('subject.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VCardText>
      <VRow>
        <VCol cols="12" md="3">
          <VTextField
            v-model="search"
            :label="t('subject.search')"
            prepend-inner-icon="bx-search"
            clearable
            density="compact"
          />
        </VCol>

        <VCol cols="12" md="2">
          <VSelect
            v-model="isActiveFilter"
            :items="filterOptions.active"
            :label="t('subject.fields.is_active')"
            density="compact"
            clearable
          />
        </VCol>

        <VCol cols="12" md="2">
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
      :items="subjects.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.name="{ item }">
        <div class="d-flex align-center">
          <div
            :style="{
              width: '12px',
              height: '12px',
              borderRadius: '50%',
              backgroundColor: item.color || '#2563eb',
              marginRight: '8px'
            }"
          />
          <span>{{ item.name || '-' }}</span>
        </div>
      </template>

      <template #item.subject_type="{ item }">
        <VChip size="small">
          {{ typeLabels[item.subject_type] || item.subject_type }}
        </VChip>
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
        <IconBtn :href="`/academic/subjects/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/academic/subjects/${item.id}/edit`" :as="Link">
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
          {{ t('subject.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="subjects.meta && subjects.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="subjects.meta.current_page"
          :length="subjects.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
