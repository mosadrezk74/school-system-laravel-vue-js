<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  feeDiscounts: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  discountTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()
const { formatMoney, formatDate } = useMoney()

const search = ref(props.filters.search ?? '')
const discountType = ref(props.filters.discount_type ?? null)
const gradeLevelId = ref(props.filters.grade_level_id ?? null)
const classroomId = ref(props.filters.classroom_id ?? null)
const isActive = ref(props.filters.is_active ?? null)

const discountTypeOptions = computed(() => props.discountTypes.map(d => ({ title: d.label, value: d.value })))
const gradeLevelOptions = computed(() => props.gradeLevels.map(g => ({ title: g.name, value: g.id })))
const classroomOptions = computed(() => props.classrooms.map(c => ({ title: c.name, value: c.id })))

const statusOptions = computed(() => [
  { title: t('finance.active'), value: '1' },
  { title: t('finance.inactive'), value: '0' },
])

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (discountType.value)
    query.discount_type = discountType.value

  if (gradeLevelId.value)
    query.grade_level_id = gradeLevelId.value

  if (classroomId.value)
    query.classroom_id = classroomId.value

  if (isActive.value !== null && isActive.value !== undefined)
    query.is_active = isActive.value

  return query
}

function applyFilters() {
  router.get('/finance/fee-discounts', buildQuery(), {
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

watch([discountType, gradeLevelId, classroomId, isActive], applyFilters)

const headers = computed(() => [
  { title: t('finance.fields.name'), key: 'name' },
  { title: t('finance.fields.code'), key: 'code' },
  { title: t('finance.fields.discount_type'), key: 'discount_type' },
  { title: t('finance.fields.discount_value'), key: 'discount_value' },
  { title: t('finance.scope'), key: 'scope' },
  { title: t('finance.usage'), key: 'usage' },
  { title: t('finance.fields.is_active'), key: 'is_active' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function scopeLabel(item) {
  if (item.classroom)
    return item.classroom.name

  if (item.grade_level)
    return item.grade_level.name

  return t('finance.schoolWide')
}

function discountValueLabel(item) {
  return item.discount_type === 'percentage'
    ? `${Number(item.discount_value)}%`
    : formatMoney(item.discount_value)
}

function archive(item) {
  askConfirm({
    message: t('finance.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/finance/fee-discounts/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/finance/fee-discounts', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('finance.feeDiscounts.title')" />

  <AppPageHeader
    :title="t('finance.feeDiscounts.title')"
    :subtitle="t('finance.feeDiscounts.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/finance/fee-discounts/create"
        :as="Link"
      >
        {{ t('finance.feeDiscounts.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('finance.searchByName')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 320px; flex: 1 1 240px;"
      />

      <VSelect
        v-model="discountType"
        :label="t('finance.fields.discount_type')"
        :items="discountTypeOptions"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <VSelect
        v-model="gradeLevelId"
        :label="t('finance.fields.grade_level_id')"
        :items="gradeLevelOptions"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <VSelect
        v-model="classroomId"
        :label="t('finance.fields.classroom_id')"
        :items="classroomOptions"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <VSelect
        v-model="isActive"
        :label="t('finance.fields.is_active')"
        :items="statusOptions"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="feeDiscounts.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.code="{ item }">
        {{ item.code ?? '—' }}
      </template>

      <template #item.discount_type="{ item }">
        <VChip size="small" color="primary" variant="tonal">
          {{ item.discount_type_label ?? '—' }}
        </VChip>
      </template>

      <template #item.discount_value="{ item }">
        {{ discountValueLabel(item) }}
      </template>

      <template #item.scope="{ item }">
        <VChip size="small" variant="tonal">
          {{ scopeLabel(item) }}
        </VChip>
      </template>

      <template #item.usage="{ item }">
        {{ item.used_count }}<span v-if="item.usage_limit"> / {{ item.usage_limit }}</span>
      </template>

      <template #item.is_active="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
          variant="tonal"
        >
          {{ item.is_active ? t('finance.active') : t('finance.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/finance/fee-discounts/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/finance/fee-discounts/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-gift"
          :title="t('finance.feeDiscounts.empty')"
        />
      </template>
    </VDataTable>

    <AppPagination
      :meta="feeDiscounts.meta"
      @change="goToPage"
    />
  </VCard>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
