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
  studentFees: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  students: { type: Array, default: () => [] },
  feeStructures: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()
const { formatMoney } = useMoney()

const studentId = ref(props.filters.student_id ?? null)
const feeStructureId = ref(props.filters.fee_structure_id ?? null)
const status = ref(props.filters.status ?? null)

const studentOptions = computed(() => props.students.map(s => ({
  title: s.admission_number ? `${s.name} (${s.admission_number})` : s.name,
  value: s.id,
})))

const feeStructureOptions = computed(() => props.feeStructures.map(f => ({ title: f.name, value: f.id })))
const statusOptions = computed(() => props.statuses.map(s => ({ title: s.label, value: s.value })))

const statusColors = {
  pending: 'warning',
  invoiced: 'info',
  paid: 'success',
  cancelled: 'secondary',
}

function buildQuery(extra = {}) {
  const query = { ...extra }

  if (studentId.value)
    query.student_id = studentId.value

  if (feeStructureId.value)
    query.fee_structure_id = feeStructureId.value

  if (status.value)
    query.status = status.value

  return query
}

function applyFilters() {
  router.get('/finance/student-fees', buildQuery(), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

watch([studentId, feeStructureId, status], applyFilters)

const headers = computed(() => [
  { title: t('finance.fields.student_id'), key: 'student' },
  { title: t('finance.fields.fee_structure_id'), key: 'fee_structure' },
  { title: t('finance.fields.amount'), key: 'amount' },
  { title: t('finance.fields.discount_amount'), key: 'discount_amount' },
  { title: t('finance.finalAmount'), key: 'final_amount' },
  { title: t('finance.fields.status'), key: 'status' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('finance.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/finance/student-fees/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/finance/student-fees', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('finance.studentFees.title')" />

  <AppPageHeader
    :title="t('finance.studentFees.title')"
    :subtitle="t('finance.studentFees.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/finance/student-fees/create"
        :as="Link"
      >
        {{ t('finance.studentFees.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VSelect
        v-model="studentId"
        :label="t('finance.fields.student_id')"
        :items="studentOptions"
        density="compact"
        clearable
        style="max-inline-size: 280px; flex: 1 1 240px;"
      />

      <VSelect
        v-model="feeStructureId"
        :label="t('finance.fields.fee_structure_id')"
        :items="feeStructureOptions"
        density="compact"
        clearable
        style="max-inline-size: 240px;"
      />

      <VSelect
        v-model="status"
        :label="t('finance.fields.status')"
        :items="statusOptions"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="studentFees.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.student="{ item }">
        {{ item.student?.name ?? '—' }}
      </template>

      <template #item.fee_structure="{ item }">
        {{ item.fee_structure?.name ?? '—' }}
      </template>

      <template #item.amount="{ item }">
        {{ formatMoney(item.amount) }}
      </template>

      <template #item.discount_amount="{ item }">
        {{ formatMoney(item.discount_amount) }}
      </template>

      <template #item.final_amount="{ item }">
        <strong>{{ formatMoney(item.final_amount) }}</strong>
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="statusColors[item.status] ?? 'secondary'"
          size="small"
          variant="tonal"
        >
          {{ item.status_label ?? '—' }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/finance/student-fees/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/finance/student-fees/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-user-check"
          :title="t('finance.studentFees.empty')"
        />
      </template>
    </VDataTable>

    <AppPagination
      :meta="studentFees.meta"
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
