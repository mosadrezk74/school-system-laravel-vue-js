<script setup>
import DefaultLayoutWithVerticalNav from '@/layouts/components/DefaultLayoutWithVerticalNav.vue'
import StudentNavItems from '@modules/Portal/resources/js/components/StudentNavItems.vue'
</script>

<template>
  <VApp>
    <DefaultLayoutWithVerticalNav :nav-component="StudentNavItems">
      <slot />
    </DefaultLayoutWithVerticalNav>
  </VApp>
</template>

<style lang="scss">
@use "@layouts/styles/default-layout";
</style>
