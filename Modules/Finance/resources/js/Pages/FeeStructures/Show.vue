<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  feeStructure: { type: Object, required: true },
})

const { t } = useI18n()
const { formatMoney, formatDate } = useMoney()

const scopeLabel = computed(() => {
  if (props.feeStructure.classroom)
    return props.feeStructure.classroom.name

  if (props.feeStructure.grade_level)
    return props.feeStructure.grade_level.name

  return t('finance.schoolWide')
})
</script>

<template>
  <Head :title="feeStructure.name" />

  <AppPageHeader
    :title="feeStructure.name"
    :subtitle="`${feeStructure.fee_category?.name ?? '—'} · ${scopeLabel}`"
  >
    <template #actions>
      <VChip color="primary" variant="tonal" size="small">
        {{ feeStructure.frequency_label ?? '—' }}
      </VChip>
      <VChip
        :color="feeStructure.is_optional ? 'warning' : 'success'"
        variant="tonal"
        size="small"
      >
        {{ feeStructure.is_optional ? t('finance.optional') : t('finance.mandatory') }}
      </VChip>
      <div class="text-end">
        <p class="text-caption text-medium-emphasis mb-0">
          {{ t('finance.fields.amount') }}
        </p>
        <h5 class="text-h5 mb-0">
          {{ formatMoney(feeStructure.amount) }}
        </h5>
      </div>
      <VBtn
        variant="tonal"
        color="secondary"
        prepend-icon="bx-edit"
        :href="`/finance/fee-structures/${feeStructure.id}/edit`"
        :as="Link"
      >
        {{ t('common.edit') }}
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
                {{ t('finance.fields.fee_category_id') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ feeStructure.fee_category?.name ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.scope') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ scopeLabel }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.due_day') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ feeStructure.due_day ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.createdAt') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ formatDate(feeStructure.created_at) }}
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
            <VIcon icon="bx-time-five" />
          </template>
          <VCardTitle>{{ t('finance.sections.lateFee') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.late_fee_amount') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ feeStructure.late_fee_amount ? formatMoney(feeStructure.late_fee_amount) : '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.late_fee_days') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ feeStructure.late_fee_days ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
