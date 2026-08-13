<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppTableLink from '@/components/AppTableLink.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  refunds: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
})

const { t } = useI18n()
const { formatMoney, formatDate } = useMoney()

const headers = computed(() => [
  { title: t('finance.fields.student_id'), key: 'student' },
  { title: t('finance.fields.amount'), key: 'amount' },
  { title: t('finance.fields.refund_date'), key: 'refund_date' },
  { title: t('finance.fields.refund_method'), key: 'refund_method' },
  { title: t('finance.fields.reason'), key: 'reason' },
])

</script>

<template>
  <Head :title="t('finance.refunds_title')" />

  <AppPageHeader
    :title="t('finance.refunds_title')"
    :subtitle="t('finance.refunds_subtitle')"
  />

  <AppDataTable
    :headers="headers"
    :items="refunds.data"
    mobile-title-key="student"
    :meta="refunds.meta"
    empty-icon="bx-undo"
    :empty-title="t('finance.refunds_empty')"
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
      <AppTableLink :href="`/finance/payments/${item.payment_id}`">
        {{ item.student?.name ?? '—' }}
      </AppTableLink>
    </template>

    <template #item.amount="{ item }">
      <strong>{{ formatMoney(item.amount) }}</strong>
    </template>

    <template #item.refund_date="{ item }">
      {{ formatDate(item.refund_date) }}
    </template>

    <template #item.refund_method="{ item }">
      {{ item.refund_method_label ?? '—' }}
    </template>

    <template #item.reason="{ item }">
      {{ item.reason ?? '—' }}
    </template>
  </AppDataTable>
</template>
