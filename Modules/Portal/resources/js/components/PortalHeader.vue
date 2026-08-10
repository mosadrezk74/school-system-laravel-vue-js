<script setup>
/**
 * The portal page heading.
 *
 * Now uses the same <AppPageHeader> as the admin side rather than a card with
 * a 56px avatar, so a teacher and an administrator see the same page
 * structure. The `icon` prop is retained for call-site compatibility but is
 * no longer rendered as a large decorative avatar — the title carries the
 * page identity, and the sidebar already says which section you are in.
 */
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: null },
  icon: { type: String, default: 'bx-home-smile' },
})
</script>

<template>
  <AppPageHeader
    :title="title"
    :subtitle="subtitle"
  >
    <template
      v-if="$slots.actions"
      #actions
    >
      <slot name="actions" />
    </template>
  </AppPageHeader>
</template>
