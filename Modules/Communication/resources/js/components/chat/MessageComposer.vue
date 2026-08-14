<script setup>
/**
 * The send box: text, staged attachments and upload progress.
 *
 * Uses useForm so the multipart POST, the progress reporting and the server's
 * validation errors all arrive through the same path every other form in the
 * application uses.
 */
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AttachmentPicker from './AttachmentPicker.vue'

const props = defineProps({
  conversationId: { type: [Number, String], required: true },
  attachmentRules: { type: Object, default: () => ({}) },
  disabled: { type: Boolean, default: false },
})

const { t } = useI18n()

const BODY_MAX = 5000

const form = useForm({
  body: '',
  attachments: [],
})

const picker = ref(null)

const canSend = computed(
  () => !props.disabled
    && !form.processing
    && (form.body.trim().length > 0 || form.attachments.length > 0),
)

/** The first attachment error, whichever index it landed on. */
const attachmentError = computed(() => {
  const key = Object.keys(form.errors).find(name => name.startsWith('attachments'))

  return key ? form.errors[key] : null
})

function submit() {
  if (!canSend.value)
    return

  form.post(`/communication/chat/conversations/${props.conversationId}/messages`, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => form.reset('body', 'attachments'),
  })
}

/**
 * Enter sends, Shift+Enter breaks the line — the convention a chat user
 * expects. Composition events are left alone so an IME candidate list is not
 * submitted mid-word.
 */
function onKeydown(event) {
  if (event.key !== 'Enter' || event.shiftKey || event.isComposing)
    return

  event.preventDefault()
  submit()
}
</script>

<template>
  <div class="chat-composer">
    <VAlert
      v-if="attachmentError"
      type="error"
      variant="tonal"
      density="compact"
      class="mb-2"
    >
      {{ attachmentError }}
    </VAlert>

    <VProgressLinear
      v-if="form.progress"
      :model-value="form.progress.percentage"
      height="4"
      color="primary"
      rounded
      class="mb-2"
    />

    <AttachmentPicker
      ref="picker"
      v-model="form.attachments"
      :rules="attachmentRules"
      :disabled="disabled || form.processing"
    />

    <div class="chat-composer__row">
      <VTextarea
        v-model="form.body"
        :placeholder="t('communication.chat.messagePlaceholder')"
        :maxlength="BODY_MAX"
        :counter="form.body.length > BODY_MAX - 500 ? BODY_MAX : undefined"
        :error-messages="form.errors.body"
        :disabled="disabled || form.processing"
        rows="1"
        max-rows="6"
        auto-grow
        hide-details="auto"
        density="comfortable"
        variant="solo-filled"
        flat
        @keydown="onKeydown"
      />

      <VBtn
        icon="bx-send"
        color="primary"
        :disabled="!canSend"
        :loading="form.processing"
        :aria-label="t('communication.chat.send')"
        @click="submit"
      />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.chat-composer {
  padding: var(--app-space-3) var(--app-space-4);
  border-block-start: 1px solid rgba(var(--v-border-color), 0.12);
}

.chat-composer__row {
  display: flex;
  align-items: flex-end;
  gap: var(--app-space-2);
}
</style>
