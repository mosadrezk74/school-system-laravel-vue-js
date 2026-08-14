<script setup>
/**
 * An open conversation: the sidebar, the thread, and the composer.
 */
import { computed, onMounted, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useDisplay } from 'vuetify'
import ConversationList from '../../components/chat/ConversationList.vue'
import MessageComposer from '../../components/chat/MessageComposer.vue'
import MessageList from '../../components/chat/MessageList.vue'
import NewConversationDialog from '../../components/chat/NewConversationDialog.vue'
import ParticipantList from '../../components/chat/ParticipantList.vue'

const props = defineProps({
  conversations: { type: Object, required: true },
  conversation: { type: Object, required: true },
  participants: { type: Array, default: () => [] },
  messages: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
  filterDefinitions: { type: Array, default: () => [] },
  directory: { type: Array, default: () => [] },
  conversationTypes: { type: Array, default: () => [] },
  attachmentRules: { type: Object, default: () => ({}) },
})

const { t } = useI18n()
const { mdAndUp } = useDisplay()

const showNew = ref(false)
const showParticipants = ref(false)

const isGroup = computed(() => props.conversation.type === 'group')

/*
 * A group is described by its size. A direct thread is already titled with the
 * other person's name — repeating it here, or worse showing the viewer their
 * own, would say nothing, so it carries no subtitle.
 */
const subtitle = computed(() => (isGroup.value
  ? t('communication.chat.memberCount', props.participants.length, { named: { count: props.participants.length } })
  : ''))

/**
 * Marking read is an explicit POST, not a side effect of rendering — but
 * opening a thread *is* the user reading it, so it fires here rather than on
 * every message fetch. `preserveState`/`preserveScroll` keep the visit from
 * disturbing the thread the user just opened.
 */
function markRead() {
  if (Number(props.conversation.unread_count ?? 0) === 0)
    return

  router.post(
    `/communication/chat/conversations/${props.conversation.id}/read`,
    {},
    { preserveScroll: true, preserveState: true, only: ['conversations', 'conversation'] },
  )
}

onMounted(markRead)
watch(() => props.conversation.id, markRead)
</script>

<template>
  <Head :title="conversation.title ?? t('communication.chat.title')" />

  <VCard class="chat-shell">
    <div class="chat-shell__grid">
      <ConversationList
        v-show="mdAndUp"
        :conversations="conversations"
        :filters="filters"
        :filter-definitions="filterDefinitions"
        :active-id="conversation.id"
        @new="showNew = true"
      />

      <section class="chat-thread">
        <header class="chat-thread__header">
          <VBtn
            v-if="!mdAndUp"
            icon="bx-arrow-back"
            variant="text"
            density="comfortable"
            href="/communication/chat"
            :as="Link"
            :aria-label="t('common.back')"
          />

          <VAvatar
            size="40"
            :color="isGroup ? 'secondary' : 'primary'"
            variant="tonal"
          >
            <VIcon
              :icon="isGroup ? 'bx-group' : 'bx-user'"
              size="20"
            />
          </VAvatar>

          <div class="chat-thread__identity">
            <h2 class="text-body-1 font-weight-medium mb-0">
              {{ conversation.title ?? t('communication.chat.untitled') }}
            </h2>
            <span class="text-caption text-medium-emphasis">{{ subtitle }}</span>
          </div>

          <VSpacer />

          <VBtn
            icon="bx-group"
            variant="text"
            density="comfortable"
            :aria-label="t('communication.chat.participants')"
            @click="showParticipants = true"
          />
        </header>

        <VDivider />

        <MessageList :messages="messages" />

        <MessageComposer
          :conversation-id="conversation.id"
          :attachment-rules="attachmentRules"
        />
      </section>
    </div>
  </VCard>

  <VNavigationDrawer
    v-model="showParticipants"
    location="end"
    temporary
    width="300"
  >
    <ParticipantList :participants="participants" />
  </VNavigationDrawer>

  <NewConversationDialog
    v-model="showNew"
    :directory="directory"
    :types="conversationTypes"
  />
</template>

<style lang="scss" scoped>
.chat-shell {
  /*
   * Fills what the layout leaves: 64px navbar, 1.5rem padding above and below,
   * and the footer. `dvh` rather than `vh` so a mobile browser collapsing its
   * address bar does not leave the composer under the fold.
   *
   * There is deliberately no tall min-height. A 32rem floor was pushing the
   * card past the bottom of a short window and scrolling the whole page, which
   * put the composer out of reach; the panes inside already scroll on their
   * own, so the shell can simply take the room it is given.
   */
  block-size: calc(100dvh - 12rem);
  min-block-size: 20rem;
  overflow: hidden;
}

.chat-shell__grid {
  display: grid;
  block-size: 100%;
  grid-template-columns: minmax(280px, 340px) 1fr;
}

.chat-thread {
  display: flex;
  flex-direction: column;
  min-block-size: 0;
}

.chat-thread__header {
  display: flex;
  align-items: center;
  padding: var(--app-space-3) var(--app-space-4);
  gap: var(--app-space-3);
}

.chat-thread__identity {
  display: flex;
  flex-direction: column;
  min-inline-size: 0;
}

@media (max-width: 959px) {
  .chat-shell__grid {
    grid-template-columns: 1fr;
  }
}
</style>
