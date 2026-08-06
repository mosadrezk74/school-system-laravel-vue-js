<script setup>
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  schoolSetting: { type: Object, default: null },
  submitRoute: { type: String, required: true },
  submitLabel: { type: String, default: null },
  onSaved: { type: Function, default: null },
})

const { t } = useI18n()

const form = useForm({
  enabled: props.schoolSetting?.settings?.ai_translation_enabled ?? true,
  provider: props.schoolSetting?.settings?.ai_translation_provider ?? 'libretranslate',
})

function submit() {
  form.post(props.submitRoute, {
    preserveScroll: true,
    onSuccess: () => props.onSaved?.(),
  })
}
</script>

<template>
  <form @submit.prevent="submit">
    <VRow>
      <VCol cols="12" md="6" class="d-flex flex-column justify-center">
        <VSwitch
          v-model="form.enabled"
          :label="t('onboarding.fields.aiTranslationEnabled')"
        />
        <p class="text-caption text-medium-emphasis mb-0">
          {{ t('onboarding.hints.aiTranslationEnabled') }}
        </p>
      </VCol>

      <VCol cols="12" md="6">
        <VSelect
          v-model="form.provider"
          :items="[{ title: 'LibreTranslate', value: 'libretranslate' }]"
          :label="t('onboarding.fields.aiTranslationProvider')"
          :error-messages="form.errors.provider"
        />
      </VCol>
    </VRow>

    <div class="d-flex justify-end mt-4">
      <VBtn :loading="form.processing" @click="submit">
        {{ submitLabel || t('common.save') }}
      </VBtn>
    </div>
  </form>
</template>
