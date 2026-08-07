<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: null },
  icon: { type: String, default: 'bx-home-smile' },
})
</script>

<template>
  <VCard>
    <VCardText class="d-flex flex-wrap align-center gap-4">
      <VAvatar color="primary" variant="tonal" size="56">
        <VIcon :icon="icon" size="32" />
      </VAvatar>

      <div class="flex-grow-1">
        <h5 class="text-h5 mb-0">
          {{ title }}
        </h5>
        <p
          v-if="subtitle"
          class="text-body-2 text-medium-emphasis mb-0 mt-1"
        >
          {{ subtitle }}
        </p>
      </div>

      <slot name="actions" />
    </VCardText>
  </VCard>
</template>
