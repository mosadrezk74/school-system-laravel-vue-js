<script setup>
import { useI18n } from 'vue-i18n'
import ModulesGrid from './Partials/_ModulesGrid.vue'

defineProps({
  modules: { type: Array, required: true },
})

const emit = defineEmits(['next'])

const { t } = useI18n()
</script>

<template>
  <h6 class="text-h6 mb-1">
    {{ t('onboarding.steps.modulesTitle') }}
  </h6>
  <p class="text-body-2 text-medium-emphasis mb-6">
    {{ t('onboarding.steps.modulesDescription') }}
  </p>

  <ModulesGrid
    :modules="modules"
    :toggle-route="name => `/onboarding/modules/${name}/toggle`"
  />

  <div class="d-flex justify-end mt-4">
    <VBtn @click="emit('next')">
      {{ t('onboarding.actions.next') }}
    </VBtn>
  </div>
</template>
