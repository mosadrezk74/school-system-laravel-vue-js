<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  feeDiscount: { type: Object, required: true },
})

const { t } = useI18n()
const { formatMoney, formatDate } = useMoney()

const scopeLabel = computed(() => {
  if (props.feeDiscount.classroom)
    return props.feeDiscount.classroom.name

  if (props.feeDiscount.grade_level)
    return props.feeDiscount.grade_level.name

  return t('finance.schoolWide')
})

const discountValueLabel = computed(() => (props.feeDiscount.discount_type === 'percentage'
  ? `${Number(props.feeDiscount.discount_value)}%`
  : formatMoney(props.feeDiscount.discount_value)))
</script>

<template>
  <Head :title="feeDiscount.name" />

  <AppPageHeader
    :title="feeDiscount.name"
    :subtitle="`${feeDiscount.code ?? '—'} · ${scopeLabel}`"
  >
    <template #actions>
      <VChip color="primary" variant="tonal" size="small">
        {{ feeDiscount.discount_type_label ?? '—' }}
      </VChip>
      <VChip
        :color="feeDiscount.is_active ? 'success' : 'secondary'"
        variant="tonal"
        size="small"
      >
        {{ feeDiscount.is_active ? t('finance.active') : t('finance.inactive') }}
      </VChip>
      <div class="text-end">
        <p class="text-caption text-medium-emphasis mb-0">
          {{ t('finance.fields.discount_value') }}
        </p>
        <h5 class="text-h5 mb-0">
          {{ discountValueLabel }}
        </h5>
      </div>
      <VBtn
        variant="tonal"
        color="secondary"
        prepend-icon="bx-edit"
        :href="`/finance/fee-discounts/${feeDiscount.id}/edit`"
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
                {{ t('finance.fields.max_amount') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ feeDiscount.max_amount ? formatMoney(feeDiscount.max_amount) : '—' }}
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
                {{ t('finance.usage') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ feeDiscount.used_count }}<span v-if="feeDiscount.usage_limit"> / {{ feeDiscount.usage_limit }}</span>
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
            <VIcon icon="bx-calendar" />
          </template>
          <VCardTitle>{{ t('finance.sections.validity') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.start_date') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ formatDate(feeDiscount.start_date) }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.end_date') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ formatDate(feeDiscount.end_date) }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.createdAt') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ formatDate(feeDiscount.created_at) }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
