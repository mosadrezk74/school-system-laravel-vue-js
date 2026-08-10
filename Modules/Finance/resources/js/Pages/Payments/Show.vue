<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  payment: { type: Object, required: true },
})

const { t } = useI18n()
const { formatMoney, formatDateTime } = useMoney()
</script>

<template>
  <Head :title="t('finance.payments.title')" />

  <AppPageHeader
    :title="formatMoney(payment.amount)"
    :subtitle="`${payment.student?.name ?? '—'} · ${formatDateTime(payment.paid_at)}`"
  >
    <template #actions>
      <VChip color="primary" variant="tonal" size="small">
        {{ payment.payment_method_label ?? '—' }}
      </VChip>
      <VBtn
        v-if="payment.invoice"
        variant="tonal"
        color="secondary"
        prepend-icon="bx-receipt"
        :href="`/finance/invoices/${payment.invoice_id}`"
        :as="Link"
      >
        {{ t('finance.viewInvoice') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VRow>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-detail" />
          </template>
          <VCardTitle>{{ t('finance.sections.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.reference_number') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.reference_number ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.admissionNumber') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.student?.admission_number ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.notes') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.notes ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-receipt" />
          </template>
          <VCardTitle>{{ t('finance.fields.invoice_id') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.invoice_number') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.invoice?.invoice_number ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.total_amount') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.invoice ? formatMoney(payment.invoice.total_amount) : '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.balance_due') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ payment.invoice ? formatMoney(payment.invoice.balance_due) : '—' }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
