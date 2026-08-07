<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'

const props = defineProps({
  invoices: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  students: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
})

const { t } = useI18n()
const { formatMoney, formatDate } = useMoney()

const search = ref(props.filters.search ?? '')
const studentId = ref(props.filters.student_id ?? null)
const status = ref(props.filters.status ?? null)
const issuedFrom = ref(props.filters.issued_from ?? '')
const issuedTo = ref(props.filters.issued_to ?? '')

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

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (studentId.value)
    query.student_id = studentId.value

  if (status.value)
    query.status = status.value

  if (issuedFrom.value)
    query.issued_from = issuedFrom.value

  if (issuedTo.value)
    query.issued_to = issuedTo.value

  return query
}

function applyFilters() {
  router.get('/finance/invoices', buildQuery(), {
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

watch([studentId, status, issuedFrom, issuedTo], applyFilters)

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
  if (!confirm(t('finance.confirmArchive')))
    return

  router.delete(`/finance/invoices/${item.id}`, { preserveScroll: true })
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

function goToPage(page) {
  router.get('/finance/invoices', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('finance.invoices.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.invoices.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.invoices.subtitle') }}</VCardSubtitle>

      <template #append>
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
    </VCardItem>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('finance.searchInvoice')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 280px; flex: 1 1 240px;"
      />

      <VSelect
        v-model="studentId"
        :label="t('finance.fields.student_id')"
        :items="studentOptions"
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
        style="max-inline-size: 180px;"
      />

      <VTextField
        v-model="issuedFrom"
        type="date"
        :label="t('finance.issuedFrom')"
        density="compact"
        clearable
        style="max-inline-size: 180px;"
      />

      <VTextField
        v-model="issuedTo"
        type="date"
        :label="t('finance.issuedTo')"
        density="compact"
        clearable
        style="max-inline-size: 180px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="invoices.data"
      :items-per-page="-1"
      hide-default-footer
    >
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
        <IconBtn :href="`/finance/invoices/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/finance/invoices/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-receipt" size="40" class="mb-2" />
          {{ t('finance.invoices.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="invoices.meta && invoices.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="invoices.meta.current_page"
          :length="invoices.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>

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
            <VTextField
              v-model="generateForm.issue_date"
              type="date"
              :label="t('finance.fields.issue_date')"
              :error-messages="generateForm.errors.issue_date"
            />
          </VCol>

          <VCol cols="12" md="6">
            <VTextField
              v-model="generateForm.due_date"
              type="date"
              :label="t('finance.fields.due_date')"
              :error-messages="generateForm.errors.due_date"
            />
          </VCol>

          <VCol cols="12">
            <VTextarea
              v-model="generateForm.notes"
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
</template>
