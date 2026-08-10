<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import DynamicFilters from '@modules/Report/resources/js/components/DynamicFilters.vue'
import DynamicTable from '@modules/Report/resources/js/components/DynamicTable.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

const props = defineProps({
  reports: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  categories: { type: Array, default: () => [] },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')
const category = ref(props.filters.category ?? null)

const categoryOptions = computed(() => props.categories.map(item => ({
  title: item.label,
  value: item.value,
})))

function applyFilters() {
  const query = {}

  if (search.value)
    query.search = search.value

  if (category.value)
    query.category = category.value

  router.get('/reports', query, {
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

watch(category, applyFilters)

const selected = ref(null)
const filterValues = ref({})
const result = ref(null)
const loading = ref(false)
const errors = ref({})
const errorMessage = ref(null)

function selectReport(report) {
  selected.value = report
  filterValues.value = {}
  result.value = null
  errors.value = {}
  errorMessage.value = null
}

function clearSelection() {
  selected.value = null
  result.value = null
}

async function run() {
  if (!selected.value)
    return

  loading.value = true
  errors.value = {}
  errorMessage.value = null

  try {
    const response = await window.fetch('/reports/generate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify({
        code: selected.value.code,
        filters: filterValues.value,
      }),
    })

    const payload = await response.json()

    if (!response.ok) {
      errors.value = payload.errors ?? {}
      errorMessage.value = payload.message ?? t('report.generationFailed')

      return
    }

    result.value = payload
  } catch {
    errorMessage.value = t('report.generationFailed')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Head :title="t('report.title')" />

  <VRow>
    <VCol cols="12">
  <AppPageHeader
    :title="t('report.title')"
    :subtitle="t('report.subtitle')"
  >
    <template #actions>
      <VBtn
        variant="tonal"
        color="secondary"
        prepend-icon="bx-cog"
        href="/reports/templates"
        :as="Link"
      >
        {{ t('report.templates.title') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

        <VDivider />

        <VCardText class="d-flex flex-wrap gap-4">
          <VTextField
            v-model="search"
            :label="t('report.searchReports')"
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
            style="max-inline-size: 220px;"
          />
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-list-ul" />
          </template>
          <VCardTitle>{{ t('report.available') }}</VCardTitle>
        </VCardItem>

        <VDivider />

        <VList v-if="reports.length" density="compact">
          <VListItem
            v-for="report in reports"
            :key="report.code"
            :active="selected?.code === report.code"
            @click="selectReport(report)"
          >
            <VListItemTitle>{{ report.name }}</VListItemTitle>
            <VListItemSubtitle>{{ report.code }}</VListItemSubtitle>
          </VListItem>
        </VList>

        <AppEmptyState v-else
          icon="bx-bar-chart-alt-2"
          :title="t('report.empty')"
        />
      </VCard>
    </VCol>

    <VCol cols="12" md="8">
      <VCard v-if="selected">
        <VCardItem>
          <VCardTitle>{{ selected.name }}</VCardTitle>
          <VCardSubtitle>{{ selected.code }}</VCardSubtitle>

          <template #append>
            <IconBtn @click="clearSelection">
              <VIcon icon="bx-x" />
            </IconBtn>
          </template>
        </VCardItem>

        <VDivider />

        <VCardText>
          <DynamicFilters
            v-model="filterValues"
            :filters="selected.filters"
            :errors="errors"
          />
        </VCardText>

        <VCardText v-if="errorMessage" class="pt-0">
          <VAlert type="error" variant="tonal" density="compact">
            {{ errorMessage }}
          </VAlert>
        </VCardText>

        <VDivider />

        <VCardText class="d-flex justify-end gap-3">
          <VBtn
            :loading="loading"
            prepend-icon="bx-play"
            @click="run"
          >
            {{ t('report.run') }}
          </VBtn>
        </VCardText>

        <template v-if="result">
          <VDivider />
          <DynamicTable
            :columns="result.columns"
            :rows="result.rows"
            :summary="result.summary"
            :loading="loading"
          />
        </template>
      </VCard>

      <VCard v-else>
        <VCardText class="d-flex flex-column align-center pa-12 text-center text-disabled">
          <VIcon icon="bx-select-multiple" size="48" class="mb-3" />
          {{ t('report.selectPrompt') }}
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
