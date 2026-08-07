<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'

defineProps({
  studentFee: { type: Object, required: true },
})

const { t } = useI18n()
const { formatMoney, formatDate } = useMoney()

const statusColors = {
  pending: 'warning',
  invoiced: 'info',
  paid: 'success',
  cancelled: 'secondary',
}
</script>

<template>
  <Head :title="t('finance.studentFees.title')" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar color="primary" variant="tonal" size="56">
            <VIcon icon="bx-user-check" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ studentFee.student?.name ?? '—' }}
              </h5>
              <VChip
                :color="statusColors[studentFee.status] ?? 'secondary'"
                variant="tonal"
                size="small"
              >
                {{ studentFee.status_label ?? '—' }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ studentFee.fee_structure?.name ?? '—' }}
              · {{ studentFee.fee_structure?.fee_category?.name ?? '—' }}
            </p>
          </div>

          <div class="text-end">
            <p class="text-caption text-medium-emphasis mb-0">
              {{ t('finance.finalAmount') }}
            </p>
            <h5 class="text-h5 mb-0">
              {{ formatMoney(studentFee.final_amount) }}
            </h5>
          </div>

          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="bx-edit"
            :href="`/finance/student-fees/${studentFee.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-money" />
          </template>
          <VCardTitle>{{ t('finance.sections.amount') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.amount') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ formatMoney(studentFee.amount) }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.fields.discount_amount') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ formatMoney(studentFee.discount_amount) }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.finalAmount') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ formatMoney(studentFee.final_amount) }}
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
            <VIcon icon="bx-detail" />
          </template>
          <VCardTitle>{{ t('finance.sections.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.admissionNumber') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ studentFee.student?.admission_number ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('finance.createdAt') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ formatDate(studentFee.created_at) }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
