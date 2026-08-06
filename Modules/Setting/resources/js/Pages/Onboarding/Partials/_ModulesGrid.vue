<script setup>
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modules: { type: Array, required: true },
  toggleRoute: { type: Function, required: true },
})

const { t } = useI18n()

function toggle(module) {
  if (!module.canBeDisabled)
    return

  router.patch(props.toggleRoute(module.name), {}, { preserveScroll: true })
}
</script>

<template>
  <VRow>
    <VCol
      v-for="module in modules"
      :key="module.name"
      cols="12"
      sm="6"
      md="4"
    >
      <VCard variant="outlined">
        <VCardText class="d-flex align-center gap-3">
          <VAvatar :color="module.enabled ? 'primary' : 'secondary'" variant="tonal" size="40">
            <VIcon :icon="module.icon" />
          </VAvatar>
          <div class="flex-grow-1">
            <div class="text-body-1 font-weight-medium">
              {{ module.name }}
            </div>
            <div v-if="module.description" class="text-caption text-medium-emphasis">
              {{ module.description }}
            </div>
            <div v-else-if="!module.canBeDisabled" class="text-caption text-medium-emphasis">
              {{ t('onboarding.modules.foundational') }}
            </div>
          </div>
          <VSwitch
            :model-value="module.enabled"
            :disabled="!module.canBeDisabled"
            hide-details
            @update:model-value="toggle(module)"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
