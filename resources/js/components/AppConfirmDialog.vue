<script setup>
/**
 * Replaces the native `confirm()` that 41 pages used for archive/restore.
 *
 * A browser confirm is unstyled, ignores the app's theme, renders its buttons
 * in the *browser's* language rather than the app's, and always lays out
 * left-to-right regardless of the Arabic interface around it. It is also the
 * one dialog a user cannot tell apart from a phishing prompt.
 */
import { useI18n } from 'vue-i18n'

const props = defineProps({
  title: { type: String, default: null },
  message: { type: String, default: null },

  /** Confirm-button colour; `error` for anything destructive. */
  color: { type: String, default: 'error' },
  confirmText: { type: String, default: null },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['confirm'])

const isOpen = defineModel({ type: Boolean, default: false })

const { t } = useI18n()

const confirm = () => {
  emit('confirm')
  isOpen.value = false
}
</script>

<template>
  <VDialog
    v-model="isOpen"
    max-width="440"
  >
    <VCard>
      <VCardItem>
        <VCardTitle>{{ title ?? t('common.confirm') }}</VCardTitle>
      </VCardItem>

      <VCardText
        v-if="message"
        class="text-body-2 text-medium-emphasis pt-0"
      >
        {{ message }}
      </VCardText>

      <VCardActions class="px-5 pb-4 pt-2">
        <VSpacer />

        <VBtn
          variant="text"
          color="secondary"
          @click="isOpen = false"
        >
          {{ t('common.cancel') }}
        </VBtn>

        <VBtn
          :color="props.color"
          variant="flat"
          :loading="loading"
          @click="confirm"
        >
          {{ confirmText ?? t('common.confirm') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
