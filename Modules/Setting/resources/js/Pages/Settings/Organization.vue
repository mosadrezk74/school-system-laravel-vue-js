<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import OrganizationForm from '../Onboarding/Partials/_OrganizationForm.vue'

defineProps({
  schoolSetting: { type: Object, default: null },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('onboarding.settings.organizationTitle')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('onboarding.settings.organizationTitle') }}</VCardTitle>
      <VCardSubtitle>{{ t('onboarding.settings.organizationSubtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <OrganizationForm
        :school-setting="schoolSetting"
        submit-route="/settings/organization"
      />
    </VCardText>
  </VCard>
</template>
