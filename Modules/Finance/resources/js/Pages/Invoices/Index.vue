<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import AppDateField from '@/components/AppDateField.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  invoices: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
  students: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()
const { formatMoney, formatDate } = useMoney()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/finance/invoices',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})


const studentOptions = computed(() => props.students.map(s => ({
  title: s.admission_number ? `${s.name} (${s.admission_number})` : s.name,
  value: s.id,
})))

const statusOptions = computed(() => props.statuses.map(s => ({ title: s.label, value: s.value })))

const statusColors = {
  pending: 'warning',
  partial: 'info',
  paid: 'success',
  cancelled: 'secondary',
  overdue: 'error',
}





const headers = computed(() => [
  { title: t('finance.fields.invoice_number'), key: 'invoice_number' },
  { title: t('finance.fields.student_id'), key: 'student' },
  { title: t('finance.fields.issue_date'), key: 'issue_date' },
  { title: t('finance.fields.due_date'), key: 'due_date' },
  { title: t('finance.fields.total_amount'), key: 'total_amount' },
  { title: t('finance.fields.paid_amount'), key: 'paid_amount' },
  { title: t('finance.fields.balance_due'), key: 'balance_due' },
  { title: t('finance.fields.status'), key: 'status' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('finance.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/finance/invoices/${item.id}`, { preserveScroll: true })
    },
  })
}

const generateDialog = ref(false)

const today = new Date().toISOString().slice(0, 10)

const generateForm = useForm({
  student_id: null,
  issue_date: today,
  due_date: today,
  notes: '',
})

function openGenerate() {
  generateForm.clearErrors()
  generateDialog.value = true
}

function submitGenerate() {
  generateForm.post('/finance/invoices/generate', {
    preserveScroll: true,
    onSuccess: () => {
      generateDialog.value = false
      generateForm.reset()
    },
  })
}

</script>

<template>
  <Head :title="t('finance.invoices.title')" />

  <AppPageHeader
    :title="t('finance.invoices.title')"
    :subtitle="t('finance.invoices.subtitle')"
  >
    <template #actions>
      <div class="d-flex gap-3">
        <VBtn
          variant="tonal"
          color="secondary"
          prepend-icon="bx-receipt"
          @click="openGenerate"
        >
          {{ t('finance.invoices.generate') }}
        </VBtn>
        <VBtn
          prepend-icon="bx-plus"
          href="/finance/invoices/create"
          :as="Link"
        >
          {{ t('finance.invoices.addNew') }}
        </VBtn>
      </div>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="invoices.data"
    mobile-title-key="invoice_number"
    :meta="invoices.meta"
    empty-icon="bx-receipt"
    :empty-title="t('finance.invoices.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('finance.searchInvoice')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <AppFilterBar
        :definitions="filterDefinitions"
        :filters="filters"
        :active-keys="activeKeys"
        @clear="clear"
        @clear-all="reset"
      />
    </template>

      <template #item.invoice_number="{ item }">
        <AppTableLink :href="`/finance/invoices/${item.id}`">
          {{ item.invoice_number ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.student="{ item }">
        {{ item.student?.name ?? '—' }}
      </template>

      <template #item.issue_date="{ item }">
        {{ formatDate(item.issue_date) }}
      </template>

      <template #item.due_date="{ item }">
        {{ formatDate(item.due_date) }}
      </template>

      <template #item.total_amount="{ item }">
        {{ formatMoney(item.total_amount, item.currency) }}
      </template>

      <template #item.paid_amount="{ item }">
        {{ formatMoney(item.paid_amount, item.currency) }}
      </template>

      <template #item.balance_due="{ item }">
        <strong>{{ formatMoney(item.balance_due, item.currency) }}</strong>
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
        <IconBtn :href="`/finance/invoices/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>
  </AppDataTable>

  <VDialog v-model="generateDialog" max-width="560">
    <VCard>
      <VCardItem>
        <VCardTitle>{{ t('finance.invoices.generate') }}</VCardTitle>
        <VCardSubtitle>{{ t('finance.invoices.generateDesc') }}</VCardSubtitle>
      </VCardItem>

      <VDivider />

      <VCardText>
        <VRow>
          <VCol cols="12">
            <VSelect
              v-model="generateForm.student_id"
              :items="studentOptions"
              :label="t('finance.fields.student_id')"
              :error-messages="generateForm.errors.student_id"
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppDateField
              v-model="generateForm.issue_date"
              :label="t('finance.fields.issue_date')"
              :error-messages="generateForm.errors.issue_date"
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppDateField
              v-model="generateForm.due_date"
              :label="t('finance.fields.due_date')"
              :error-messages="generateForm.errors.due_date"
            />
          </VCol>

          <VCol cols="12">
            <VTextarea
              v-model="generateForm.notes"
              maxlength="5000"
              :counter="5000"
              rows="2"
              :label="t('finance.fields.notes')"
              :error-messages="generateForm.errors.notes"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex justify-end gap-3">
        <VBtn
          variant="tonal"
          color="secondary"
          @click="generateDialog = false"
        >
          {{ t('common.cancel') }}
        </VBtn>
        <VBtn :loading="generateForm.processing" @click="submitGenerate">
          {{ t('finance.invoices.generate') }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
