<script setup>
import DefaultLayoutWithVerticalNav from './components/DefaultLayoutWithVerticalNav.vue'

/*
 * VApp lives here rather than in a root App.vue: Inertia swaps the layout
 * component itself, so every layout must provide Vuetify's root wrapper.
 */
</script>

<template>
  <VApp>
    <DefaultLayoutWithVerticalNav>
      <slot />
    </DefaultLayoutWithVerticalNav>
  </VApp>
</template>

<style lang="scss">
// As we are using `layouts` plugin we need its styles to be imported
@use "@layouts/styles/default-layout";
</style>
