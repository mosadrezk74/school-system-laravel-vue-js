<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  message: { type: Object, required: true },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="message.title" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar color="primary" variant="tonal" size="56">
            <VIcon icon="bx-message-detail" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ message.title }}
              </h5>
              <VChip :color="message.status === 'sent' ? 'success' : 'secondary'" size="small">
                {{ t(`communication.messages.statuses.${message.status}`) }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ message.sender?.name ?? '—' }}
              · {{ t(`communication.audience.${message.target_type}`) }}
            </p>
          </div>

          <VBtn
            variant="tonal"
            color="secondary"
            :href="'/communication/messages'"
            :as="Link"
          >
            {{ t('common.back') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

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
