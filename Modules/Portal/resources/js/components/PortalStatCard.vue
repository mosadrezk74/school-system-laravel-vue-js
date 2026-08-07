<script setup>
defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], default: null },
  icon: { type: String, default: 'bx-bar-chart-alt-2' },
  color: { type: String, default: 'primary' },
  hint: { type: String, default: null },
})

const format = value => (value === null || value === undefined ? '—' : value)
</script>

<template>
  <VCard>
    <VCardText class="d-flex align-center gap-4">
      <VAvatar
        :color="color"
        variant="tonal"
        size="48"
      >
        <VIcon :icon="icon" size="26" />
      </VAvatar>
      <div>
        <p class="text-caption text-medium-emphasis mb-1">
          {{ title }}
        </p>
        <h5 class="text-h5 mb-0">
          {{ format(value) }}
        </h5>
        <p
          v-if="hint"
          class="text-caption text-medium-emphasis mb-0 mt-1"
        >
          {{ hint }}
        </p>
      </div>
    </VCardText>
  </VCard>
</template>
