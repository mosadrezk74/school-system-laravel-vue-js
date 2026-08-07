<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import ChildSelector from '@modules/Portal/resources/js/components/ChildSelector.vue'

defineOptions({ layout: ParentLayout })

const props = defineProps({
  resources: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  types: { type: Array, default: () => [] },
  children: { type: Array, default: () => [] },
  selectedChild: { type: [Number, null], default: null },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')
const type = ref(props.filters.type ?? null)

const typeOptions = computed(() => props.types.map(item => ({ title: item.label, value: item.value })))

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (type.value)
    query.type = type.value

  return query
}

function applyFilters() {
  router.get('/parent/materials', buildQuery(), {
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

watch(type, applyFilters)

const headers = computed(() => [
  { title: t('portal.parent.materialTitle'), key: 'title' },
  { title: t('portal.parent.materialType'), key: 'type_label' },
  { title: t('portal.parent.downloads'), key: 'downloads_count', align: 'end' },
  { title: t('portal.parent.uploadedAt'), key: 'created_at' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])


function formatDate(value) {
  return value ? new Date(value).toLocaleDateString() : '—'
}

function goToPage(page) {
  router.get('/parent/materials', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('portal.nav.studyMaterials')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.studyMaterials')"
        :subtitle="t('portal.parent.materialsSubtitle')"
        icon="bx-folder-open"
      >
        <template #actions>
          <ChildSelector :children="children" :selected="selectedChild" url="/parent/materials" />
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap gap-4">
          <VTextField
            v-model="search"
            :label="t('portal.parent.searchMaterials')"
            prepend-inner-icon="bx-search"
            density="compact"
            clearable
            hide-details
            style="max-inline-size: 320px; flex: 1 1 240px;"
          />

          <VSelect
            v-model="type"
            :items="typeOptions"
            :label="t('portal.parent.materialType')"
            density="compact"
            clearable
            hide-details
            style="max-inline-size: 220px;"
          />
        </VCardText>

        <VDataTable
          :headers="headers"
          :items="resources.data"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.type_label="{ item }">
            <VChip size="small" color="primary" variant="tonal">
              {{ item.type_label ?? '—' }}
            </VChip>
          </template>

          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #item.actions="{ item }">
            <IconBtn
              v-if="item.has_file"
              :href="`/parent/materials/${item.id}/download`"
            >
              <VIcon icon="bx-download" />
            </IconBtn>
            <IconBtn
              v-if="item.external_url"
              :href="item.external_url"
              target="_blank"
              rel="noopener noreferrer"
            >
              <VIcon icon="bx-link-external" />
            </IconBtn>
          </template>

          <template #no-data>
            <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
              <VIcon icon="bx-folder-open" size="40" class="mb-2" />
              {{ t('portal.parent.noMaterials') }}
            </div>
          </template>
        </VDataTable>

        <template v-if="resources.meta && resources.meta.last_page > 1">
          <VDivider />
          <div class="d-flex justify-end pa-4">
            <VPagination
              :model-value="resources.meta.current_page"
              :length="resources.meta.last_page"
              :total-visible="5"
              density="compact"
              @update:model-value="goToPage"
            />
          </div>
        </template>
      </VCard>
    </VCol>
  </VRow>
</template>
