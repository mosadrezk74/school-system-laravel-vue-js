<script setup>
/**
 * The title block that opens every page.
 *
 * Replaces the `VCardItem > VCardTitle + VCardSubtitle + #append` pattern that
 * 124 files had each rewritten by hand. Lifting the title out of the card is
 * the main visual change: a heading that sits on the page background, with the
 * card below holding only content, is what gives the app a clear hierarchy
 * instead of a stack of equally-weighted boxes.
 */
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: null },
})
</script>

<template>
  <div class="app-page-header">
    <div class="app-page-header__text">
      <h1 class="text-h4 mb-0">
        {{ title }}
      </h1>
      <p
        v-if="subtitle"
        class="text-body-2 text-medium-emphasis mb-0 mt-1"
      >
        {{ subtitle }}
      </p>
    </div>

    <!--
      Actions wrap beneath the title on small screens rather than being
      squeezed alongside it, which is what previously cut off "Add new"
      buttons with long Arabic labels on a phone.
    -->
    <div
      v-if="$slots.actions"
      class="app-page-header__actions"
    >
      <slot name="actions" />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.app-page-header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--app-space-4);
  margin-block-end: var(--app-space-6);

  &__text {
    min-inline-size: 0;
  }

  &__actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: var(--app-space-2);
  }
}
</style>
