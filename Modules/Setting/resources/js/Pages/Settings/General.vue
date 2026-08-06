<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import SchoolInfoForm from '../Onboarding/Partials/_SchoolInfoForm.vue'

defineProps({
  schoolSetting: { type: Object, default: null },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('onboarding.settings.generalTitle')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('onboarding.settings.generalTitle') }}</VCardTitle>
      <VCardSubtitle>{{ t('onboarding.settings.generalSubtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <SchoolInfoForm
        :school-setting="schoolSetting"
        submit-route="/settings/general"
      />
    </VCardText>
  </VCard>
</template>
