<script setup>
import { useI18n } from 'vue-i18n'

defineEmits(['next'])

const { t } = useI18n()
</script>

<template>
  <div class="d-flex flex-column align-center text-center py-8">
    <VAvatar color="primary" variant="tonal" size="72" class="mb-4">
      <VIcon icon="bx-rocket" size="36" />
    </VAvatar>
    <h5 class="text-h5 mb-2">
      {{ t('onboarding.steps.welcomeTitle') }}
    </h5>
    <p class="text-body-1 text-medium-emphasis mb-6" style="max-inline-size: 480px;">
      {{ t('onboarding.steps.welcomeDescription') }}
    </p>
    <VBtn @click="$emit('next')">
      {{ t('onboarding.actions.getStarted') }}
    </VBtn>
  </div>
</template>
