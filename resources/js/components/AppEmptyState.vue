<script setup>
/**
 * The one empty state for the whole application.
 *
 * 51 files had each written their own `<div class="pa-8 text-center
 * text-disabled">`, which meant every module's "nothing here" moment looked
 * slightly different and none of them offered a way forward. This one gives
 * the user an icon, an explanation and an optional action.
 */
defineProps({
  icon: { type: String, default: 'bx-folder-open' },
  title: { type: String, required: true },
  description: { type: String, default: null },
})
</script>

<template>
  <div class="app-empty-state">
    <VAvatar
      size="52"
      rounded="lg"
      color="primary"
      variant="tonal"
    >
      <VIcon
        :icon="icon"
        size="26"
      />
    </VAvatar>

    <p class="text-body-1 font-weight-medium mb-0 mt-4">
      {{ title }}
    </p>

    <p
      v-if="description"
      class="text-body-2 text-medium-emphasis mb-0 mt-1"
    >
      {{ description }}
    </p>

    <div
      v-if="$slots.action"
      class="mt-4"
    >
      <slot name="action" />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.app-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding-block: var(--app-space-12);
  padding-inline: var(--app-space-6);
  text-align: center;
}
</style>
