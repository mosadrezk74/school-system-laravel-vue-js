<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AiTranslationForm from '../Onboarding/Partials/_AiTranslationForm.vue'

defineProps({
  schoolSetting: { type: Object, default: null },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('onboarding.settings.aiTranslationTitle')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('onboarding.settings.aiTranslationTitle') }}</VCardTitle>
      <VCardSubtitle>{{ t('onboarding.settings.aiTranslationSubtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <AiTranslationForm
        :school-setting="schoolSetting"
        submit-route="/settings/ai-translation"
      />
    </VCardText>
  </VCard>
</template>
