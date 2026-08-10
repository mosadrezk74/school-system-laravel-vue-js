<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

defineOptions({ layout: ParentLayout })

const props = defineProps({
  invoice: { type: Object, required: true },
  paymentMethods: { type: Object, required: true },
})

const { t } = useI18n()

const invoice = computed(() => props.invoice.data ?? props.invoice)
const methods = computed(() => props.paymentMethods.data ?? props.paymentMethods)

const money = value => new Intl.NumberFormat(undefined, {
  style: 'currency',
  currency: invoice.value.currency ?? 'SAR',
  minimumFractionDigits: 2,
}).format(Number(value ?? 0))

const form = useForm({
  invoice_id: invoice.value.id,
  payment_method_id: null,
  notes: '',
})

const selectedMethod = computed(() =>
  methods.value.find(method => method.id === form.payment_method_id) ?? null)

/*
 * The amount is display only — it is never posted. The server reads the
 * invoice balance when it creates the request, so what is shown here cannot
 * become what is charged.
 */
const amountDue = computed(() => invoice.value.balance_due)

function submit() {
  form.post('/parent/payment-requests', { preserveScroll: true })
}
</script>

<template>
  <Head :title="t('finance.pay_now')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('finance.pay_now')"
        :subtitle="invoice.invoice_number"
        icon="bx-credit-card"
      />
    </VCol>

    <VCol cols="12" md="5">
      <VCard>
        <VCardText>
          <div class="text-body-2 text-medium-emphasis">
            {{ t('portal.parent.student') }}
          </div>
          <div class="text-h6 mb-4">
            {{ invoice.student?.name ?? '—' }}
          </div>

          <VDivider class="mb-4" />

          <div class="d-flex justify-space-between mb-2">
            <span class="text-medium-emphasis">{{ t('finance.original_amount') }}</span>
            <span>{{ money(invoice.subtotal) }}</span>
          </div>

          <div
            v-if="Number(invoice.discount_amount ?? 0) > 0"
            class="d-flex justify-space-between mb-2"
          >
            <span class="text-medium-emphasis">{{ t('finance.fields.discount_amount') }}</span>
            <span>-{{ money(invoice.discount_amount) }}</span>
          </div>

          <div class="d-flex justify-space-between mb-2">
            <span class="text-medium-emphasis">{{ t('finance.fields.paid_amount') }}</span>
            <span>{{ money(invoice.paid_amount) }}</span>
          </div>

          <VDivider class="my-3" />

          <div class="d-flex justify-space-between">
            <strong>{{ t('finance.amount_due') }}</strong>
            <strong class="text-h6">{{ money(amountDue) }}</strong>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="7">
      <VCard>
        <VCardText>
          <h6 class="text-h6 mb-4">
            {{ t('finance.choose_payment_method') }}
          </h6>

          <AppEmptyState
            v-if="methods.length === 0"
            icon="bx-credit-card"
            :title="t('finance.payment_methods_empty')"
          />

          <template v-else>
            <VRadioGroup
              v-model="form.payment_method_id"
              :error-messages="form.errors.payment_method_id"
            >
              <VRadio
                v-for="method in methods"
                :key="method.id"
                :value="method.id"
                :label="method.name"
              />
            </VRadioGroup>

            <!--
              The stored configuration for the chosen method: bank details or a
              payment link, never a value hardcoded in this page.
            -->
            <VAlert
              v-if="selectedMethod"
              variant="tonal"
              class="mb-4"
            >
              <div
                v-if="selectedMethod.type === 'bank_transfer'"
                class="mb-2"
              >
                <div v-if="selectedMethod.bank_name">
                  <strong>{{ t('finance.fields.bank_name') }}:</strong> {{ selectedMethod.bank_name }}
                </div>
                <div v-if="selectedMethod.account_name">
                  <strong>{{ t('finance.fields.account_name') }}:</strong> {{ selectedMethod.account_name }}
                </div>
                <div v-if="selectedMethod.account_number">
                  <strong>{{ t('finance.fields.account_number') }}:</strong> {{ selectedMethod.account_number }}
                </div>
                <div v-if="selectedMethod.iban">
                  <strong>{{ t('finance.fields.iban') }}:</strong> {{ selectedMethod.iban }}
                </div>
              </div>

              <div
                v-if="selectedMethod.instructions"
                style="white-space: pre-line"
              >
                {{ selectedMethod.instructions }}
              </div>
            </VAlert>

            <VTextarea
              v-model="form.notes"
              :label="t('finance.fields.notes')"
              rows="2"
              :error-messages="form.errors.notes"
            />
          </template>
        </VCardText>

        <VCardActions class="px-4 pb-4">
          <VSpacer />
          <VBtn
            variant="tonal"
            color="secondary"
            href="/parent/fees"
            :as="Link"
          >
            {{ t('common.cancel') }}
          </VBtn>
          <VBtn
            :loading="form.processing"
            :disabled="!form.payment_method_id"
            @click="submit"
          >
            {{ t('finance.pay_now') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VCol>
  </VRow>
</template>
