<script setup>
import { useI18n } from 'vue-i18n'
import LanguagesTable from './Partials/_LanguagesTable.vue'

defineProps({
  languages: { type: Array, required: true },
})

const emit = defineEmits(['next'])

const { t } = useI18n()
</script>

<template>
  <div class="d-flex flex-column align-center text-center mb-8">
    <VAvatar color="primary" variant="tonal" size="72" class="mb-4">
      <VIcon icon="bx-rocket" size="36" />
    </VAvatar>
    <h5 class="text-h5 mb-2">
      {{ t('onboarding.steps.welcomeTitle') }}
    </h5>
    <p class="text-body-1 text-medium-emphasis" style="max-inline-size: 480px;">
      {{ t('onboarding.steps.welcomeDescription') }}
    </p>
  </div>

  <h6 class="text-h6 mb-1">
    {{ t('onboarding.steps.languagesTitle') }}
  </h6>
  <p class="text-body-2 text-medium-emphasis mb-6">
    {{ t('onboarding.steps.languagesDescription') }}
  </p>

  <LanguagesTable
    :rows="languages"
    :toggle-route="language => `/onboarding/languages/${language.id}/toggle`"
    :default-route="language => `/onboarding/languages/${language.id}/default`"
  />

  <div class="d-flex justify-end mt-4">
    <VBtn @click="emit('next')">
      {{ t('onboarding.actions.getStarted') }}
    </VBtn>
  </div>
</template>
