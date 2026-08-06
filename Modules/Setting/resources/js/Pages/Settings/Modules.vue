<script setup>
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ModulesGrid from '../Onboarding/Partials/_ModulesGrid.vue'

defineProps({
  modules: { type: Array, required: true },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('onboarding.settings.modulesTitle')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('onboarding.settings.modulesTitle') }}</VCardTitle>
      <VCardSubtitle>{{ t('onboarding.settings.modulesSubtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <ModulesGrid
        :modules="modules"
        :toggle-route="name => `/settings/modules/${name}/toggle`"
      />
    </VCardText>
  </VCard>
</template>
