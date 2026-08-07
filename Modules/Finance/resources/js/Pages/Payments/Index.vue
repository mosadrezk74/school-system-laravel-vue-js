<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'

const props = defineProps({
  payments: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  invoices: { type: Array, default: () => [] },
  paymentMethods: { type: Array, default: () => [] },
})

const { t } = useI18n()
const { formatMoney, formatDateTime } = useMoney()

const search = ref(props.filters.search ?? '')
const paymentMethod = ref(props.filters.payment_method ?? null)
const paidFrom = ref(props.filters.paid_from ?? '')
const paidTo = ref(props.filters.paid_to ?? '')

const paymentMethodOptions = computed(() => props.paymentMethods.map(m => ({ title: m.label, value: m.value })))

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (paymentMethod.value)
    query.payment_method = paymentMethod.value

  if (paidFrom.value)
    query.paid_from = paidFrom.value

  if (paidTo.value)
    query.paid_to = paidTo.value

  return query
}

function applyFilters() {
  router.get('/finance/payments', buildQuery(), {
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

watch([paymentMethod, paidFrom, paidTo], applyFilters)

const headers = computed(() => [
  { title: t('finance.fields.paid_at'), key: 'paid_at' },
  { title: t('finance.fields.student_id'), key: 'student' },
  { title: t('finance.fields.invoice_id'), key: 'invoice' },
  { title: t('finance.fields.payment_method'), key: 'payment_method' },
  { title: t('finance.fields.reference_number'), key: 'reference_number' },
  { title: t('finance.fields.amount'), key: 'amount' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  if (!confirm(t('finance.confirmArchivePayment')))
    return

  router.delete(`/finance/payments/${item.id}`, { preserveScroll: true })
}

function goToPage(page) {
  router.get('/finance/payments', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('finance.payments.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('finance.payments.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('finance.payments.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          href="/finance/payments/create"
          :as="Link"
        >
          {{ t('finance.payments.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText class="d-flex flex-wrap gap-4">
      <VTextField
        v-model="search"
        :label="t('finance.searchPayment')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 300px; flex: 1 1 240px;"
      />

      <VSelect
        v-model="paymentMethod"
        :label="t('finance.fields.payment_method')"
        :items="paymentMethodOptions"
        density="compact"
        clearable
        style="max-inline-size: 200px;"
      />

      <VTextField
        v-model="paidFrom"
        type="date"
        :label="t('finance.paidFrom')"
        density="compact"
        clearable
        style="max-inline-size: 180px;"
      />

      <VTextField
        v-model="paidTo"
        type="date"
        :label="t('finance.paidTo')"
        density="compact"
        clearable
        style="max-inline-size: 180px;"
      />
    </VCardText>

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
        <Link
          v-if="item.invoice"
          :href="`/finance/invoices/${item.invoice_id}`"
        >
          {{ item.invoice.invoice_number }}
        </Link>
        <span v-else>—</span>
      </template>

      <template #item.payment_method="{ item }">
        <VChip size="small" color="primary" variant="tonal">
          {{ item.payment_method_label ?? '—' }}
        </VChip>
      </template>

      <template #item.reference_number="{ item }">
        {{ item.reference_number ?? '—' }}
      </template>

      <template #item.amount="{ item }">
        <strong>{{ formatMoney(item.amount) }}</strong>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/finance/payments/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-credit-card" size="40" class="mb-2" />
          {{ t('finance.payments.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="payments.meta && payments.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="payments.meta.current_page"
          :length="payments.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
