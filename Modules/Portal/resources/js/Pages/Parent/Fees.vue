<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

defineOptions({ layout: ParentLayout })

const props = defineProps({
  invoices: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  statuses: { type: Array, default: () => [] },
  children: { type: Array, default: () => [] },
})

const { t } = useI18n()

const money = value => new Intl.NumberFormat(undefined, {
  style: 'currency',
  currency: 'SAR',
  minimumFractionDigits: 2,
}).format(Number(value ?? 0))

const totalOutstanding = computed(() => (props.invoices.data ?? [])
  .reduce((sum, row) => sum + Number(row.balance_due ?? 0), 0))

const statusColors = {
  pending: 'warning',
  partial: 'info',
  paid: 'success',
  cancelled: 'secondary',
  overdue: 'error',
}

const headers = computed(() => [
  { title: t('portal.parent.invoiceNumber'), key: 'invoice_number' },
  { title: t('portal.parent.student'), key: 'student' },
  { title: t('portal.parent.dueDate'), key: 'due_date' },
  { title: t('portal.parent.total'), key: 'total_amount', align: 'end' },
  { title: t('portal.parent.balance'), key: 'balance_due', align: 'end' },
  { title: t('portal.parent.status'), key: 'status' },
])

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString() : '—'
}

function goToPage(page) {
  router.get('/parent/fees', { page }, { preserveState: true, preserveScroll: true })
}
</script>

<template>
  <Head :title="t('portal.nav.fees')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.fees')"
        :subtitle="t('portal.parent.feesSubtitle')"
        icon="bx-receipt"
      >
        <template #actions>
          <VChip color="error" variant="tonal" size="large">
            {{ t('portal.parent.outstanding') }}: {{ money(totalOutstanding) }}
          </VChip>
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VDataTable
          :headers="headers"
          :items="invoices.data"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.student="{ item }">
            {{ item.student?.name ?? '—' }}
          </template>

          <template #item.due_date="{ item }">
            {{ formatDate(item.due_date) }}
          </template>

          <template #item.total_amount="{ item }">
            {{ money(item.total_amount) }}
          </template>

          <template #item.balance_due="{ item }">
            <strong>{{ money(item.balance_due) }}</strong>
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

      <template #no-data>
        <AppEmptyState
          icon="bx-receipt"
          :title="t('portal.parent.noInvoices')"
        />
      </template>
        </VDataTable>

    <AppPagination
      :meta="invoices.meta"
      @change="goToPage"
    />
      </VCard>
    </VCol>
  </VRow>
</template>
