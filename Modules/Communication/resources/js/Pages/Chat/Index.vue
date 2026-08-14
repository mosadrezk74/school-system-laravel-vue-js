<script setup>
/**
 * Chat with nothing open yet: the sidebar, and a prompt to pick a thread.
 *
 * Show.vue renders the same sidebar beside an open conversation, so the two
 * pages differ only in what fills the right-hand panel.
 */
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppEmptyState from '@/components/AppEmptyState.vue'
import ConversationList from '../../components/chat/ConversationList.vue'
import NewConversationDialog from '../../components/chat/NewConversationDialog.vue'

defineProps({
  conversations: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterDefinitions: { type: Array, default: () => [] },
  directory: { type: Array, default: () => [] },
  conversationTypes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const showNew = ref(false)
</script>

<template>
  <Head :title="t('communication.chat.title')" />

  <VCard class="chat-shell">
    <div class="chat-shell__grid">
      <ConversationList
        :conversations="conversations"
        :filters="filters"
        :filter-definitions="filterDefinitions"
        @new="showNew = true"
      />

      <div class="chat-shell__panel">
        <AppEmptyState
          icon="bx-conversation"
          :title="t('communication.chat.selectConversation')"
          :description="t('communication.chat.selectConversationHint')"
        >
          <template #action>
            <VBtn
              color="primary"
              prepend-icon="bx-plus"
              @click="showNew = true"
            >
              {{ t('communication.chat.newConversation') }}
            </VBtn>
          </template>
        </AppEmptyState>
      </div>
    </div>
  </VCard>

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

.chat-shell__panel {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-block-size: 0;
}

@media (max-width: 959px) {
  .chat-shell__grid {
    grid-template-columns: 1fr;
  }

  /* On a phone the list is the page; the panel appears only once a thread is open. */
  .chat-shell__panel {
    display: none;
  }
}
</style>
