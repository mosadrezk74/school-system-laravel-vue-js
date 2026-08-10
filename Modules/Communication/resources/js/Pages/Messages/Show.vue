<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  message: { type: Object, required: true },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="message.title" />

  <AppPageHeader
    :title="message.title"
    :subtitle="`${message.sender?.name ?? '—'} · ${t(`communication.audience.${message.target_type}`)}`"
  >
    <template #actions>
      <VChip :color="message.status === 'sent' ? 'success' : 'secondary'" size="small">
        {{ t(`communication.messages.statuses.${message.status}`) }}
      </VChip>
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/communication/messages'"
        :as="Link"
      >
        {{ t('common.back') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VRow>

    <VCol cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-detail" />
          </template>
          <VCardTitle>{{ t('communication.messages.body') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          {{ message.body }}
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
