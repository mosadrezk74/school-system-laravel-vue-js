<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  paymentMethod: { type: Object, required: true },
})

const { t } = useI18n()

const record = computed(() => props.paymentMethod.data ?? props.paymentMethod)

const isBankTransfer = computed(() => record.value.type === 'bank_transfer')
const isOnline = computed(() => record.value.type === 'online')
</script>

<template>
  <Head :title="record.name" />

  <AppPageHeader
    :title="record.name"
    :subtitle="record.type_label"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-edit"
        :href="`/finance/payment-methods/${record.id}/edit`"
        :as="Link"
      >
        {{ t('common.edit') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>
    <VCardText>
      <VRow>
        <VCol cols="12" md="6">
          <div class="text-body-2 text-medium-emphasis">
            {{ t('finance.fields.code') }}
          </div>
          <div>{{ record.code }}</div>
        </VCol>

        <VCol cols="12" md="6">
          <div class="text-body-2 text-medium-emphasis">
            {{ t('finance.fields.is_active') }}
          </div>
          <VChip :color="record.is_active ? 'success' : 'secondary'" size="small">
            {{ record.is_active ? t('common.active') : t('common.inactive') }}
          </VChip>
        </VCol>

        <VCol v-if="record.description" cols="12">
          <div class="text-body-2 text-medium-emphasis">
            {{ t('finance.fields.description') }}
          </div>
          <div>{{ record.description }}</div>
        </VCol>

        <VCol v-if="isOnline && record.payment_url" cols="12">
          <div class="text-body-2 text-medium-emphasis">
            {{ t('finance.fields.payment_url') }}
          </div>
          <div class="text-break">
            {{ record.payment_url }}
          </div>
        </VCol>

        <template v-if="isBankTransfer">
          <VCol cols="12" md="6">
            <div class="text-body-2 text-medium-emphasis">
              {{ t('finance.fields.bank_name') }}
            </div>
            <div>{{ record.bank_name ?? '—' }}</div>
          </VCol>

          <VCol cols="12" md="6">
            <div class="text-body-2 text-medium-emphasis">
              {{ t('finance.fields.account_name') }}
            </div>
            <div>{{ record.account_name ?? '—' }}</div>
          </VCol>

          <VCol cols="12" md="6">
            <div class="text-body-2 text-medium-emphasis">
              {{ t('finance.fields.account_number') }}
            </div>
            <div>{{ record.account_number ?? '—' }}</div>
          </VCol>

          <VCol cols="12" md="6">
            <div class="text-body-2 text-medium-emphasis">
              {{ t('finance.fields.iban') }}
            </div>
            <div>{{ record.iban ?? '—' }}</div>
          </VCol>
        </template>

        <VCol v-if="record.instructions" cols="12">
          <div class="text-body-2 text-medium-emphasis">
            {{ t('finance.fields.instructions') }}
          </div>
          <div style="white-space: pre-line">
            {{ record.instructions }}
          </div>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
