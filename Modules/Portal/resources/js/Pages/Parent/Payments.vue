<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

defineOptions({ layout: ParentLayout })

defineProps({
  payments: { type: Object, required: true },
})

const { t } = useI18n()

const money = value => new Intl.NumberFormat(undefined, {
  style: 'currency',
  currency: 'SAR',
  minimumFractionDigits: 2,
}).format(Number(value ?? 0))

const headers = computed(() => [
  { title: t('portal.parent.paidAt'), key: 'paid_at' },
  { title: t('portal.parent.student'), key: 'student' },
  { title: t('portal.parent.invoiceNumber'), key: 'invoice' },
  { title: t('portal.parent.method'), key: 'payment_method_label' },
  { title: t('portal.parent.amount'), key: 'amount', align: 'end' },
])

function formatDateTime(value) {
  return value ? new Date(value).toLocaleString() : '—'
}

function goToPage(page) {
  router.get('/parent/payments', { page }, { preserveState: true, preserveScroll: true })
}
</script>

<template>
  <Head :title="t('portal.nav.payments')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.payments')"
        :subtitle="t('portal.parent.paymentsSubtitle')"
        icon="bx-credit-card"
      />
    </VCol>

    <VCol cols="12">
      <VCard>
        <VDataTable
          :headers="headers"
          :items="payments.data"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.paid_at="{ item }">
            {{ formatDateTime(item.paid_at) }}
          </template>

          <template #item.student="{ item }">
            {{ item.student?.name ?? '—' }}
          </template>

          <template #item.invoice="{ item }">
            {{ item.invoice?.invoice_number ?? '—' }}
          </template>

          <template #item.amount="{ item }">
            <strong>{{ money(item.amount) }}</strong>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-credit-card"
          :title="t('portal.parent.noPayments')"
        />
      </template>
        </VDataTable>

    <AppPagination
      :meta="payments.meta"
      @change="goToPage"
    />
      </VCard>
    </VCol>
  </VRow>
</template>
