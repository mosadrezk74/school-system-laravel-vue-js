<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  feeStructures: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  feeCategories: { type: Array, default: () => [] },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  frequencies: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()
const { formatMoney } = useMoney()

const search = ref(props.filters.search ?? '')
const feeCategoryId = ref(props.filters.fee_category_id ?? null)
const gradeLevelId = ref(props.filters.grade_level_id ?? null)
const classroomId = ref(props.filters.classroom_id ?? null)
const frequency = ref(props.filters.frequency ?? null)

const feeCategoryOptions = computed(() => props.feeCategories.map(c => ({ title: c.name, value: c.id })))
const gradeLevelOptions = computed(() => props.gradeLevels.map(g => ({ title: g.name, value: g.id })))
const classroomOptions = computed(() => props.classrooms.map(c => ({ title: c.name, value: c.id })))
const frequencyOptions = computed(() => props.frequencies.map(f => ({ title: f.label, value: f.value })))

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (feeCategoryId.value)
    query.fee_category_id = feeCategoryId.value

  if (gradeLevelId.value)
    query.grade_level_id = gradeLevelId.value

  if (classroomId.value)
    query.classroom_id = classroomId.value

  if (frequency.value)
    query.frequency = frequency.value

  return query
}

function applyFilters() {
  router.get('/finance/fee-structures', buildQuery(), {
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

watch([feeCategoryId, gradeLevelId, classroomId, frequency], applyFilters)

const headers = computed(() => [
  { title: t('finance.fields.name'), key: 'name' },
  { title: t('finance.fields.fee_category_id'), key: 'fee_category' },
  { title: t('finance.scope'), key: 'scope' },
  { title: t('finance.fields.amount'), key: 'amount' },
  { title: t('finance.fields.frequency'), key: 'frequency' },
  { title: t('finance.fields.is_optional'), key: 'is_optional' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function scopeLabel(item) {
  if (item.classroom)
    return item.classroom.name

  if (item.grade_level)
    return item.grade_level.name

  return t('finance.schoolWide')
}

function archive(item) {
  askConfirm({
    message: t('finance.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/finance/fee-structures/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/finance/fee-structures', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('finance.feeStructures.title')" />

  <AppPageHeader
    :title="t('finance.feeStructures.title')"
    :subtitle="t('finance.feeStructures.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/finance/fee-structures/create"
        :as="Link"
      >
        {{ t('finance.feeStructures.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="feeStructures.data"
    mobile-title-key="name"
    :meta="feeStructures.meta"
    empty-icon="bx-layer"
    :empty-title="t('finance.feeStructures.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="search"
        :label="t('finance.searchByName')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <VSelect
        v-model="feeCategoryId"
        :label="t('finance.fields.fee_category_id')"
        :items="feeCategoryOptions"
        clearable
      />

      <VSelect
        v-model="gradeLevelId"
        :label="t('finance.fields.grade_level_id')"
        :items="gradeLevelOptions"
        clearable
      />

      <VSelect
        v-model="classroomId"
        :label="t('finance.fields.classroom_id')"
        :items="classroomOptions"
        clearable
      />

      <VSelect
        v-model="frequency"
        :label="t('finance.fields.frequency')"
        :items="frequencyOptions"
        clearable
      />
    </template>

      <template #item.name="{ item }">
        <AppTableLink :href="`/finance/fee-structures/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.fee_category="{ item }">
        {{ item.fee_category?.name ?? '—' }}
      </template>

      <template #item.scope="{ item }">
        <VChip size="small" variant="tonal">
          {{ scopeLabel(item) }}
        </VChip>
      </template>

      <template #item.amount="{ item }">
        {{ formatMoney(item.amount) }}
      </template>

      <template #item.frequency="{ item }">
        <VChip size="small" color="primary" variant="tonal">
          {{ item.frequency_label ?? '—' }}
        </VChip>
      </template>

      <template #item.is_optional="{ item }">
        <VChip
          :color="item.is_optional ? 'warning' : 'success'"
          size="small"
          variant="tonal"
        >
          {{ item.is_optional ? t('finance.optional') : t('finance.mandatory') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/finance/fee-structures/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
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
