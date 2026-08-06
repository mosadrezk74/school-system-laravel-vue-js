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
      {{ t('onboarding.actions.next') }}
    </VBtn>
  </div>
</template>
