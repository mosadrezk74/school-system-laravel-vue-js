<script setup>
/**
 * The message history of the open conversation.
 *
 * The server paginates newest-first, so page 1 is the bottom of the thread.
 * <InfiniteScroll> is given `reverse`, which is its chat mode: it flips the
 * loading directions so scrolling *up* fetches older pages, and scrolls to the
 * bottom on first render. Reversing the array for display is left to us, which
 * is what `ordered` does.
 */
import { computed } from 'vue'
import { InfiniteScroll } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppEmptyState from '@/components/AppEmptyState.vue'
import MessageItem from './MessageItem.vue'

const props = defineProps({
  messages: { type: Object, default: () => ({ data: [] }) },
})

const { t, locale } = useI18n()

/** Oldest first for reading; the server's order is newest first. */
const ordered = computed(() => [...(props.messages?.data ?? [])].reverse())

/**
 * Each row carries the two things the bubble cannot work out alone: whether it
 * opens a new day, and whether it repeats the previous sender.
 */
const rows = computed(() => ordered.value.map((message, index) => {
  const previous = ordered.value[index - 1]

  return {
    message,
    showSender: previous?.sender?.id !== message.sender?.id,
    dayLabel: startsNewDay(previous, message) ? formatDay(message.sent_at) : null,
  }
}))

function startsNewDay(previous, message) {
  if (!message.sent_at)
    return false
  if (!previous?.sent_at)
    return true

  return new Date(previous.sent_at).toDateString() !== new Date(message.sent_at).toDateString()
}

function formatDay(value) {
  return new Intl.DateTimeFormat(locale.value, { dateStyle: 'medium' }).format(new Date(value))
}
</script>

<template>
  <div class="chat-messages">
    <AppEmptyState
      v-if="!rows.length"
      icon="bx-message-rounded"
      :title="t('communication.chat.noMessages')"
      :description="t('communication.chat.noMessagesHint')"
    />

    <InfiniteScroll
      v-else
      data="messages"
      reverse
      class="chat-messages__list"
    >
      <template
        v-for="row in rows"
        :key="row.message.id"
      >
        <div
          v-if="row.dayLabel"
          class="chat-messages__day"
        >
          <VChip
            size="x-small"
            variant="tonal"
          >
            {{ row.dayLabel }}
          </VChip>
        </div>

        <MessageItem
          :message="row.message"
          :show-sender="row.showSender"
        />
      </template>

      <template #loading>
        <div class="chat-messages__loading">
          <VProgressCircular
            indeterminate
            size="22"
            width="2"
          />
          <span class="text-caption text-medium-emphasis">{{ t('communication.chat.loadingOlder') }}</span>
        </div>
      </template>
    </InfiniteScroll>
  </div>
</template>

<style lang="scss" scoped>
.chat-messages {
  flex: 1 1 auto;
  padding: var(--app-space-4);
  overflow-y: auto;
}

.chat-messages__list {
  display: flex;
  flex-direction: column;
  gap: var(--app-space-3);
}

.chat-messages__day {
  align-self: center;
  margin-block: var(--app-space-2);
}

.chat-messages__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-block: var(--app-space-3);
  gap: var(--app-space-2);
}
</style>
