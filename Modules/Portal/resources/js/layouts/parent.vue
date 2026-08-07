<script setup>
import DefaultLayoutWithVerticalNav from '@/layouts/components/DefaultLayoutWithVerticalNav.vue'
import ParentNavItems from '@modules/Portal/resources/js/components/ParentNavItems.vue'
</script>

<template>
  <VApp>
    <DefaultLayoutWithVerticalNav :nav-component="ParentNavItems">
      <slot />
    </DefaultLayoutWithVerticalNav>
  </VApp>
</template>

<style lang="scss">
@use "@layouts/styles/default-layout";
</style>
