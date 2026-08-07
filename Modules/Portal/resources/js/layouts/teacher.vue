<script setup>
import DefaultLayoutWithVerticalNav from '@/layouts/components/DefaultLayoutWithVerticalNav.vue'
import TeacherNavItems from '@modules/Portal/resources/js/components/TeacherNavItems.vue'
</script>

<template>
  <VApp>
    <DefaultLayoutWithVerticalNav :nav-component="TeacherNavItems">
      <slot />
    </DefaultLayoutWithVerticalNav>
  </VApp>
</template>

<style lang="scss">
@use "@layouts/styles/default-layout";
</style>
