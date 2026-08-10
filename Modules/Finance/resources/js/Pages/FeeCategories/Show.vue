<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  feeCategory: { type: Object, required: true },
})

const { t } = useI18n()
const { formatDate } = useMoney()
</script>

<template>
  <Head :title="feeCategory.name" />

  <AppPageHeader
    :title="feeCategory.name"
    :subtitle="feeCategory.code ?? '—'"
  >
    <template #actions>
      <VChip
        :color="feeCategory.is_active ? 'success' : 'secondary'"
        variant="tonal"
        size="small"
      >
        {{ feeCategory.is_active ? t('finance.active') : t('finance.inactive') }}
      </VChip>
      <VBtn
        variant="tonal"
        color="secondary"
        prepend-icon="bx-edit"
        :href="`/finance/fee-categories/${feeCategory.id}/edit`"
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
                {{ t('finance.fields.description') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ feeCategory.description ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.code') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ feeCategory.code ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.createdAt') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ formatDate(feeCategory.created_at) }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
