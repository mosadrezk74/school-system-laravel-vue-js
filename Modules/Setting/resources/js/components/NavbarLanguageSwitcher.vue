<script setup>
import { useAppLocale } from '@modules/Setting/resources/js/composable/useAppLocale'

const { locale, currentLocale, locales, setLocale } = useAppLocale()
</script>

<template>
  <IconBtn>
    <VIcon icon="bx-globe" />

    <VTooltip
      activator="parent"
      open-delay="1000"
      scroll-strategy="close"
    >
      <span>{{ currentLocale.label }}</span>
    </VTooltip>

    <VMenu
      activator="parent"
      offset="12px"
      width="180"
    >
      <VList
        :selected="[locale]"
        density="compact"
      >
        <VListItem
          v-for="item in locales"
          :key="item.code"
          :value="item.code"
          @click="setLocale(item.code)"
        >
          <VListItemTitle>{{ item.label }}</VListItemTitle>
        </VListItem>
      </VList>
    </VMenu>
  </IconBtn>
</template>
