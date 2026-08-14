<script setup>
/**
 * One message bubble.
 *
 * Alignment follows `is_mine`, which the server sets by comparing the sender
 * to the authenticated user — the page never decides whose message this is.
 * Because the bubble is aligned with flex `align-self`, it flips correctly
 * under RTL without a direction-specific rule.
 */
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AttachmentPreview from './AttachmentPreview.vue'

const props = defineProps({
  message: { type: Object, required: true },

  /** False when the previous bubble has the same sender, to avoid repeating it. */
  showSender: { type: Boolean, default: true },
})

const { t, locale } = useI18n()

const isSystem = computed(() => props.message.type === 'system')

const attachments = computed(() => props.message.attachments ?? [])

const timestamp = computed(() => {
  if (!props.message.sent_at)
    return ''

  return new Intl.DateTimeFormat(locale.value, {
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(props.message.sent_at))
})
</script>

<template>
  <!-- System notices sit centred and unattributed. -->
  <div
    v-if="isSystem"
    class="chat-message chat-message--system"
  >
    <span class="text-caption text-medium-emphasis">{{ message.body }}</span>
  </div>

  <div
    v-else
    class="chat-message"
    :class="message.is_mine ? 'chat-message--mine' : 'chat-message--theirs'"
  >
    <span
      v-if="showSender && !message.is_mine"
      class="chat-message__sender text-caption text-medium-emphasis"
    >
      {{ message.sender?.name ?? t('communication.chat.unknownSender') }}
    </span>

    <div class="chat-message__bubble">
      <p
        v-if="message.body"
        class="chat-message__body"
      >
        {{ message.body }}
      </p>

      <div
        v-if="attachments.length"
        class="chat-message__attachments"
      >
        <AttachmentPreview
          v-for="attachment in attachments"
          :key="attachment.id"
          :attachment="attachment"
        />
      </div>

      <span class="chat-message__time text-caption app-numeric">{{ timestamp }}</span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.chat-message {
  display: flex;
  flex-direction: column;
  max-inline-size: min(80%, 560px);
  gap: 2px;
}

.chat-message--mine {
  align-self: flex-end;
  align-items: flex-end;
}

.chat-message--theirs {
  align-self: flex-start;
  align-items: flex-start;
}

.chat-message--system {
  align-self: center;
  align-items: center;
  max-inline-size: 100%;
  text-align: center;
}

.chat-message__sender {
  padding-inline: var(--app-space-2);
}

.chat-message__bubble {
  display: flex;
  flex-direction: column;
  padding: var(--app-space-3);
  border-radius: 12px;
  background-color: rgb(var(--v-theme-surface));
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
  gap: var(--app-space-2);
}

.chat-message--mine .chat-message__bubble {
  background-color: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
}

.chat-message__body {
  margin: 0;

  /* Preserves the line breaks a user typed without allowing any markup. */
  white-space: pre-wrap;
  overflow-wrap: anywhere;
}

.chat-message__attachments {
  display: flex;
  flex-direction: column;
  gap: var(--app-space-2);
}

.chat-message__time {
  align-self: flex-end;
  opacity: 0.7;
}
</style>
