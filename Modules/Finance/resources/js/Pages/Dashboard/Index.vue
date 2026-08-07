<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'

const props = defineProps({
  statistics: { type: Object, required: true },
})

const { t } = useI18n()
const { formatMoney } = useMoney()

const cards = computed(() => [
  {
    key: 'total_outstanding',
    title: t('finance.dashboard.totalOutstanding'),
    value: formatMoney(props.statistics.total_outstanding),
    icon: 'bx-wallet',
    color: 'warning',
  },
  {
    key: 'total_paid',
    title: t('finance.dashboard.totalPaid'),
    value: formatMoney(props.statistics.total_paid),
    icon: 'bx-check-circle',
    color: 'success',
  },
  {
    key: 'todays_payments',
    title: t('finance.dashboard.todaysPayments'),
    value: formatMoney(props.statistics.todays_payments),
    icon: 'bx-calendar-check',
    color: 'info',
  },
  {
    key: 'monthly_revenue',
    title: t('finance.dashboard.monthlyRevenue'),
    value: formatMoney(props.statistics.monthly_revenue),
    icon: 'bx-trending-up',
    color: 'primary',
  },
  {
    key: 'pending_invoices',
    title: t('finance.dashboard.pendingInvoices'),
    value: props.statistics.pending_invoices,
    icon: 'bx-time-five',
    color: 'secondary',
  },
  {
    key: 'overdue_invoices',
    title: t('finance.dashboard.overdueInvoices'),
    value: props.statistics.overdue_invoices,
    icon: 'bx-error-circle',
    color: 'error',
  },
])

const reports = computed(() => [
  { key: 'outstanding-fees', title: t('finance.reports.outstandingFees'), icon: 'bx-wallet' },
  { key: 'student-ledger', title: t('finance.reports.studentLedger'), icon: 'bx-book-content' },
  { key: 'daily-payments', title: t('finance.reports.dailyPayments'), icon: 'bx-calendar-check' },
  { key: 'monthly-revenue', title: t('finance.reports.monthlyRevenue'), icon: 'bx-bar-chart-alt-2' },
  { key: 'category-revenue', title: t('finance.reports.categoryRevenue'), icon: 'bx-pie-chart-alt-2' },
])
</script>

<template>
  <Head :title="t('finance.dashboard.title')" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('finance.dashboard.title') }}</VCardTitle>
          <VCardSubtitle>{{ t('finance.dashboard.subtitle') }}</VCardSubtitle>
        </VCardItem>
      </VCard>
    </VCol>

    <VCol
      v-for="card in cards"
      :key="card.key"
      cols="12"
      sm="6"
      md="4"
    >
      <VCard>
        <VCardText class="d-flex align-center gap-4">
          <VAvatar :color="card.color" variant="tonal" size="48">
            <VIcon :icon="card.icon" size="26" />
          </VAvatar>
          <div>
            <p class="text-caption text-medium-emphasis mb-1">
              {{ card.title }}
            </p>
            <h5 class="text-h5 mb-0">
              {{ card.value }}
            </h5>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-bar-chart-alt-2" />
          </template>
          <VCardTitle>{{ t('finance.reports.title') }}</VCardTitle>
          <VCardSubtitle>{{ t('finance.reports.subtitle') }}</VCardSubtitle>
        </VCardItem>

        <VCardText class="d-flex flex-wrap gap-3">
          <VBtn
            v-for="report in reports"
            :key="report.key"
            variant="tonal"
            color="secondary"
            :prepend-icon="report.icon"
            :href="`/finance/reports/${report.key}`"
            :as="Link"
          >
            {{ report.title }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
