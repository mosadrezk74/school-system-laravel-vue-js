<script setup>
/**
 * The conversation sidebar: search, filters, the list itself and its paginator.
 *
 * Filtering goes through the shared useIndexFilters composable against the
 * schema the ConversationQuery published, so this component adds no filtering
 * mechanism of its own and the query-string contract matches every other index
 * in the application.
 *
 * AppFilterBar is rendered inside a menu rather than inline. Its controls lay
 * out on a 12-column grid at `md=3`, and those breakpoints follow the
 * *viewport*, not the container — so in a 340px sidebar every control collapsed
 * to about 85px and the labels truncated to "الن…". The menu gives the same
 * component the width it was designed for without forking it.
 */
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppFilterBar from '@/components/AppFilterBar.vue'
import AppPagination from '@/components/AppPagination.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'
import ConversationItem from './ConversationItem.vue'

const props = defineProps({
  conversations: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterDefinitions: { type: Array, default: () => [] },
  activeId: { type: [Number, String], default: null },
})

defineEmits(['new'])

const { t } = useI18n()

const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/communication/chat',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})

/**
 * Search has its own always-visible input, so it is not counted here — the
 * badge is about filters hidden behind the menu, and counting the term the
 * user can already see would make the button look active for no reason.
 */
const activeFilterCount = computed(
  () => activeKeys.value.filter(key => key !== 'search').length,
)
</script>

<template>
  <aside class="conversation-list">
    <header class="conversation-list__header">
      <VTextField
        v-model="filters.search.value"
        :label="t('communication.chat.searchConversations')"
        maxlength="100"
        prepend-inner-icon="bx-search"
        density="compact"
        hide-details
        clearable
      />

      <VMenu
        :close-on-content-click="false"
        location="bottom end"
        min-width="340"
      >
        <template #activator="{ props: menuProps }">
          <VBadge
            :model-value="activeFilterCount > 0"
            :content="activeFilterCount"
            color="primary"
            offset-x="4"
            offset-y="4"
          >
            <VBtn
              v-bind="menuProps"
              icon="bx-filter-alt"
              variant="text"
              density="comfortable"
              :aria-label="t('common.filters')"
            />
          </VBadge>
        </template>

        <VCard min-width="340">
          <VCardText>
            <AppFilterBar
              :definitions="filterDefinitions"
              :filters="filters"
              :active-keys="activeKeys"
              stacked
              :collapse-after="filterDefinitions.length"
              @clear="clear"
              @clear-all="reset"
            />
          </VCardText>
        </VCard>
      </VMenu>

      <VBtn
        icon="bx-edit"
        color="primary"
        variant="tonal"
        density="comfortable"
        :aria-label="t('communication.chat.newConversation')"
        @click="$emit('new')"
      />
    </header>

    <VDivider />

    <div class="conversation-list__scroll">
      <AppEmptyState
        v-if="!conversations.data.length"
        icon="bx-conversation"
        :title="activeFilterCount || filters.search.value
          ? t('common.noResultsTitle')
          : t('communication.chat.noConversations')"
        :description="activeFilterCount || filters.search.value
          ? t('common.noResultsHint')
          : t('communication.chat.noConversationsHint')"
      />

      <VList
        v-else
        density="comfortable"
        lines="two"
        class="pa-2"
      >
        <ConversationItem
          v-for="conversation in conversations.data"
          :key="conversation.id"
          :conversation="conversation"
          :active="String(conversation.id) === String(activeId)"
        />
      </VList>
    </div>

    <AppPagination
      :meta="conversations.meta"
      @change="goToPage"
    />
  </aside>
</template>

<style lang="scss" scoped>
.conversation-list {
  display: flex;
  flex-direction: column;
  border-inline-end: 1px solid rgba(var(--v-border-color), 0.12);
  block-size: 100%;
  min-block-size: 0;
}

.conversation-list__header {
  display: flex;
  align-items: center;
  padding: var(--app-space-3);
  gap: var(--app-space-2);
}

.conversation-list__scroll {
  flex: 1 1 auto;
  min-block-size: 0;
  overflow-y: auto;
}
</style>
