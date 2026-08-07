<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'

const props = defineProps({
  sections: { type: Array, required: true },
})

const page = usePage()

const enabledModules = computed(() => page.props.enabledModules ?? [])

const isModuleEnabled = name => !name || enabledModules.value.includes(name)

const visibleSections = computed(() => props.sections
  .map(section => ({
    ...section,
    children: section.children.filter(child => isModuleEnabled(child.module)),
  }))
  .filter(section => section.children.length > 0))
</script>

<template>
  <template
    v-for="section in visibleSections"
    :key="section.key"
  >
    <VerticalNavSectionTitle
      v-if="section.heading"
      :item="{ heading: section.heading }"
    />
    <VerticalNavLink
      v-for="child in section.children"
      :key="child.to"
      :item="{ title: child.title, to: child.to, icon: child.icon }"
    />
  </template>
</template>
