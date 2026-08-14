<script setup>
/**
 * One row in the conversation sidebar: who, the last thing said, when, and how
 * much of it is unread.
 */
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  conversation: { type: Object, required: true },
  active: { type: Boolean, default: false },
})

const { t, locale } = useI18n()

const isGroup = computed(() => props.conversation.type === 'group')

const unread = computed(() => Number(props.conversation.unread_count ?? 0))

/**
 * The last message as one line. A file-only message has no body, so it is
 * described by its attachment count instead of rendering as blank.
 */
const preview = computed(() => {
  const last = props.conversation.last_message

  if (!last)
    return t('communication.chat.noMessagesYet')

  const attachments = last.attachments?.length ?? 0

  if (last.body)
    return last.body

  return attachments > 0
    ? t('communication.chat.attachmentCount', attachments, { named: { count: attachments } })
    : ''
})

const hasAttachments = computed(() => (props.conversation.last_message?.attachments?.length ?? 0) > 0)

/** Time for today, date for anything older — the usual messaging shorthand. */
const timestamp = computed(() => {
  const value = props.conversation.last_message_at

  if (!value)
    return ''

  const date = new Date(value)
  const isToday = date.toDateString() === new Date().toDateString()

  return new Intl.DateTimeFormat(
    locale.value,
    isToday ? { hour: '2-digit', minute: '2-digit' } : { day: '2-digit', month: 'short' },
  ).format(date)
})

const initials = computed(() => (props.conversation.title ?? '?')
  .trim()
  .split(/\s+/)
  .slice(0, 2)
  .map(part => part.charAt(0))
  .join('')
  .toUpperCase())
</script>

<template>
  <VListItem
    :href="`/communication/chat/conversations/${conversation.id}`"
    :as="Link"
    :active="active"
    class="conversation-item"
    rounded="lg"
    prefetch
  >
    <template #prepend>
      <VAvatar
        size="40"
        :color="isGroup ? 'secondary' : 'primary'"
        variant="tonal"
      >
        <VIcon
          v-if="isGroup"
          icon="bx-group"
          size="20"
        />
        <span v-else>{{ initials }}</span>
      </VAvatar>
    </template>

    <VListItemTitle class="conversation-item__title">
      {{ conversation.title ?? t('communication.chat.untitled') }}
    </VListItemTitle>

    <VListItemSubtitle class="conversation-item__preview">
      <VIcon
        v-if="hasAttachments"
        icon="bx-paperclip"
        size="14"
        class="me-1"
      />
      {{ preview }}
    </VListItemSubtitle>

    <template #append>
      <div class="conversation-item__meta">
        <span class="text-caption text-medium-emphasis app-numeric">{{ timestamp }}</span>
        <VBadge
          v-if="unread > 0"
          :content="unread > 99 ? '99+' : unread"
          color="primary"
          inline
        />
      </div>
    </template>
  </VListItem>
</template>

<style lang="scss" scoped>
.conversation-item__title {
  font-weight: 500;
}

.conversation-item__preview {
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 1;
}

.conversation-item__meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}
</style>
