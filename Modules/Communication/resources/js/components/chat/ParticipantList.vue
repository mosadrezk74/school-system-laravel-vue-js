<script setup>
/**
 * Who is in the open conversation.
 *
 * Shows the name and role only — the payload carries nothing else, so there is
 * nothing here for a curious participant to read off another family's record.
 */
import { useI18n } from 'vue-i18n'

defineProps({
  participants: { type: Array, default: () => [] },
})

const { t } = useI18n()
</script>

<template>
  <VList
    density="compact"
    class="participant-list"
  >
    <VListSubheader>{{ t('communication.chat.participants') }}</VListSubheader>

    <VListItem
      v-for="participant in participants"
      :key="participant.id"
    >
      <template #prepend>
        <VAvatar
          size="32"
          color="primary"
          variant="tonal"
        >
          <span class="text-caption">{{ (participant.name ?? '?').charAt(0).toUpperCase() }}</span>
        </VAvatar>
      </template>

      <VListItemTitle>{{ participant.name ?? t('communication.chat.unknownSender') }}</VListItemTitle>
      <VListItemSubtitle v-if="participant.role">
        {{ t(`communication.chat.roles.${participant.role}`) }}
      </VListItemSubtitle>
    </VListItem>
  </VList>
</template>

<style lang="scss" scoped>
.participant-list {
  min-inline-size: 240px;
}
</style>
