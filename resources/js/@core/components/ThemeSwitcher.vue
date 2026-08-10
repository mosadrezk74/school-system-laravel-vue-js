<script setup>
import { useTheme } from 'vuetify'
import { router, usePage } from '@inertiajs/vue3'
import { THEME_STORAGE_KEY } from '@/plugins/vuetify/theme'

const props = defineProps({
  themes: {
    type: Array,
    required: true,
  },
})

const {
  name: themeName,
  global: globalTheme,
} = useTheme()

const {
  state: currentThemeName,
  next: getNextThemeName,
  index: currentThemeIndex,
} = useCycleList(props.themes.map(t => t.name), { initialValue: themeName })

const changeTheme = () => {
  globalTheme.name.value = getNextThemeName()
}

const page = usePage()

/*
 * The user row is the source of truth so the choice follows the account across
 * devices; localStorage is only a first-paint cache, kept in sync here so a
 * reload can restore the theme before the Inertia props arrive.
 */
watch(() => globalTheme.name.value, val => {
  currentThemeName.value = val
  localStorage.setItem(THEME_STORAGE_KEY, val)

  if (!page.props.auth?.user)
    return

  router.patch('/settings/theme', { theme: val }, {
    preserveScroll: true,
    preserveState: true,
    only: [],
  })
})

/*
 * Adopt the stored theme when the account's preference differs from whatever
 * this browser last painted (fresh device, or changed elsewhere).
 */
watch(() => page.props.auth?.user?.theme, val => {
  if (val && val !== globalTheme.name.value)
    globalTheme.name.value = val
}, { immediate: true })
</script>

<template>
  <IconBtn @click="changeTheme">
    <VIcon :icon="props.themes[currentThemeIndex].icon" />
    <VTooltip
      activator="parent"
      open-delay="1000"
      scroll-strategy="close"
    >
      <span class="text-capitalize">{{ currentThemeName }}</span>
    </VTooltip>
  </IconBtn>
</template>
