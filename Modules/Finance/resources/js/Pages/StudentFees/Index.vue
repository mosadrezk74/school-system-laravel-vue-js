<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  studentFees: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
  students: { type: Array, default: () => [] },
  feeStructures: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()
const { formatMoney } = useMoney()

const studentId = ref(props.filters.student_id ?? null)
const feeStructureId = ref(props.filters.fee_structure_id ?? null)
/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/finance/student-fees',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})


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

  <AppDataTable
    :headers="headers"
    :items="studentFees.data"
    mobile-title-key="student"
    :meta="studentFees.meta"
    empty-icon="bx-user-check"
    :empty-title="t('finance.studentFees.empty')"
    @page="goToPage"
  >
    <template #filters>
      <AppFilterBar
        :definitions="filterDefinitions"
        :filters="filters"
        :active-keys="activeKeys"
        @clear="clear"
        @clear-all="reset"
      />
    </template>

      <template #item.student="{ item }">
        <AppTableLink :href="`/finance/student-fees/${item.id}`">
          {{ item.student?.name ?? '—' }}
        </AppTableLink>
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
        <IconBtn :href="`/finance/student-fees/${item.id}/edit`" :as="Link">
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
