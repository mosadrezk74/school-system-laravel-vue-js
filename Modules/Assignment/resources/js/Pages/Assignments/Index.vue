<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  assignments: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')
const published = ref(props.filters.is_published ?? null)

// Omit the key entirely when cleared, rather than sending an empty string:
// $request->has('is_published') would still be true for '', so the backend
// would read that as an explicit "false" instead of "no filter".
function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (published.value !== null)
    query.is_published = published.value

  return query
}

function applyFilters() {
  router.get('/assignments/assignments', buildQuery(), {
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

watch(published, applyFilters)

const headers = computed(() => [
  { title: t('assignments.title'), key: 'title' },
  { title: t('assignments.class'), key: 'class' },
  { title: t('assignments.subject'), key: 'subject' },
  { title: t('assignments.teacher'), key: 'teacher' },
  { title: t('assignments.deadline'), key: 'deadline' },
  { title: t('assignments.maxScore'), key: 'max_score' },
  { title: t('assignments.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  if (!confirm(t('assignments.confirmArchive')))
    return

  router.delete(`/assignments/assignments/${item.id}`, { preserveScroll: true })
}

function goToPage(page) {
  router.get('/assignments/assignments', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('assignments.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('assignments.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('assignments.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :to="'/assignments/assignments/create'"
          :href="'/assignments/assignments/create'"
          :as="Link"
        >
          {{ t('assignments.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('assignments.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 360px; flex: 1 1 260px;"
      />

      <VSelect
        v-model="published"
        :label="t('common.filter')"
        :items="[
          { title: t('common.all'), value: null },
          { title: t('assignments.published'), value: true },
          { title: t('assignments.unpublished'), value: false },
        ]"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="assignments.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.class="{ item }">
        {{ item.class ? `${item.class.name} ${item.class.section_code}` : '—' }}
      </template>

      <template #item.subject="{ item }">
        {{ item.subject?.name ?? '—' }}
      </template>

      <template #item.teacher="{ item }">
        {{ item.teacher?.name ?? '—' }}
      </template>

      <template #item.deadline="{ item }">
        {{ item.deadline ? new Date(item.deadline).toLocaleString() : '—' }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_published ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_published ? t('assignments.published') : t('assignments.unpublished') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/assignments/assignments/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/assignments/assignments/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-task" size="40" class="mb-2" />
          {{ t('assignments.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="assignments.meta && assignments.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="assignments.meta.current_page"
          :length="assignments.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
