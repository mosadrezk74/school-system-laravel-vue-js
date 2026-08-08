<script setup>
import { router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  schoolSetting: { type: Object, default: null },
  languages: { type: Array, required: true },
})

const { t } = useI18n()

const activeLanguages = computed(() => props.languages.filter(l => l.is_active))

const processing = ref(false)

function finish() {
  processing.value = true
  router.post('/onboarding/finish', {}, {
    onFinish: () => { processing.value = false },
  })
}
</script>

<template>
  <h6 class="text-h6 mb-1">
    {{ t('onboarding.steps.reviewTitle') }}
  </h6>
  <p class="text-body-2 text-medium-emphasis mb-6">
    {{ t('onboarding.steps.reviewDescription') }}
  </p>

  <VRow>
    <VCol cols="12" md="6">
      <VCard variant="outlined">
        <VCardItem>
          <VCardTitle>{{ t('onboarding.steps.schoolInfoTitle') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <div class="text-body-2">
            {{ schoolSetting?.school_name }}
          </div>
          <div class="text-caption text-medium-emphasis">
            {{ schoolSetting?.timezone }} — {{ schoolSetting?.currency }}
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard variant="outlined">
        <VCardItem>
          <VCardTitle>{{ t('onboarding.steps.languagesTitle') }}</VCardTitle>
        </VCardItem>
        <VCardText class="d-flex flex-wrap gap-2">
          <VChip
            v-for="language in activeLanguages"
            :key="language.locale"
            size="small"
            :color="language.is_default ? 'primary' : undefined"
          >
            {{ language.name }}
          </VChip>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <div class="d-flex justify-end mt-6">
    <VBtn :loading="processing" color="success" @click="finish">
      {{ t('onboarding.actions.finish') }}
    </VBtn>
  </div>
</template>
