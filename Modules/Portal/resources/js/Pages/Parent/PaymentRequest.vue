<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'

defineOptions({ layout: ParentLayout })

const props = defineProps({
  payment: { type: Object, required: true },
})

const { t } = useI18n()

const payment = computed(() => props.payment.data ?? props.payment)
const method = computed(() => payment.value.payment_method_details)

const money = value => new Intl.NumberFormat(undefined, {
  style: 'currency',
  currency: 'SAR',
  minimumFractionDigits: 2,
}).format(Number(value ?? 0))

const statusColors = {
  pending: 'warning',
  paid: 'success',
  failed: 'error',
  cancelled: 'secondary',
  refunded: 'info',
  partially_refunded: 'info',
}

/*
 * An online method sends the parent onward to the school's configured link.
 * Following it proves nothing about whether money moved, so the request stays
 * pending either way — the button is a convenience, not a state change.
 */
const paymentUrl = computed(() =>
  (method.value?.type === 'online' ? method.value?.payment_url : null))
</script>

<template>
  <Head :title="t('finance.payment_request')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('finance.payment_request')"
        :subtitle="payment.invoice?.invoice_number"
        icon="bx-receipt"
      />
    </VCol>

    <VCol cols="12" md="7">
      <VCard>
        <VCardText>
          <div class="d-flex align-center justify-space-between mb-4">
            <span class="text-h5">{{ money(payment.amount) }}</span>
            <VChip
              :color="statusColors[payment.status] ?? 'secondary'"
              variant="tonal"
            >
              {{ payment.status_label }}
            </VChip>
          </div>

          <VAlert
            v-if="payment.awaiting_confirmation"
            type="info"
            variant="tonal"
            class="mb-4"
          >
            <div class="font-weight-medium">
              {{ t('finance.awaiting_confirmation') }}
            </div>
            <div class="text-body-2">
              {{ t('finance.awaiting_confirmation_hint') }}
            </div>
          </VAlert>

          <VDivider class="mb-4" />

          <div class="d-flex justify-space-between mb-2">
            <span class="text-medium-emphasis">{{ t('portal.parent.student') }}</span>
            <span>{{ payment.student?.name ?? '—' }}</span>
          </div>

          <div class="d-flex justify-space-between mb-2">
            <span class="text-medium-emphasis">{{ t('finance.fields.payment_method') }}</span>
            <span>{{ method?.name ?? '—' }}</span>
          </div>

          <div class="d-flex justify-space-between">
            <span class="text-medium-emphasis">{{ t('finance.requested_at') }}</span>
            <span>{{ payment.created_at ? new Date(payment.created_at).toLocaleString() : '—' }}</span>
          </div>
        </VCardText>

        <VCardActions class="px-4 pb-4">
          <VBtn
            variant="tonal"
            color="secondary"
            href="/parent/fees"
            :as="Link"
          >
            {{ t('common.back') }}
          </VBtn>
          <VSpacer />
          <VBtn
            v-if="paymentUrl"
            :href="paymentUrl"
            target="_blank"
            rel="noopener noreferrer"
            prepend-icon="bx-link-external"
          >
            {{ t('finance.open_payment_link') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VCol>

    <VCol
      v-if="method?.instructions || method?.iban"
      cols="12"
      md="5"
    >
      <VCard>
        <VCardText>
          <h6 class="text-h6 mb-3">
            {{ t('finance.payment_instructions') }}
          </h6>

          <div
            v-if="method.type === 'bank_transfer'"
            class="mb-3"
          >
            <div v-if="method.bank_name">
              <strong>{{ t('finance.fields.bank_name') }}:</strong> {{ method.bank_name }}
            </div>
            <div v-if="method.account_name">
              <strong>{{ t('finance.fields.account_name') }}:</strong> {{ method.account_name }}
            </div>
            <div v-if="method.account_number">
              <strong>{{ t('finance.fields.account_number') }}:</strong> {{ method.account_number }}
            </div>
            <div v-if="method.iban">
              <strong>{{ t('finance.fields.iban') }}:</strong> {{ method.iban }}
            </div>
          </div>

          <div
            v-if="method.instructions"
            style="white-space: pre-line"
          >
            {{ method.instructions }}
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
