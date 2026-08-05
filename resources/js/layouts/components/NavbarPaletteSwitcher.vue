<script setup>
import { useI18n } from 'vue-i18n'
import { useAppPalette } from '@core/composable/useAppPalette'

const { t } = useI18n()
const { current, currentPalette, palettes, setPalette } = useAppPalette()
</script>

<template>
  <IconBtn>
    <VIcon icon="bx-palette" />

    <VTooltip
      activator="parent"
      open-delay="1000"
      scroll-strategy="close"
    >
      <span>{{ t('palette.title') }}</span>
    </VTooltip>

    <VMenu
      activator="parent"
      offset="12px"
      width="200"
    >
      <VList
        :selected="[current]"
        density="compact"
      >
        <VListSubheader>{{ t('palette.title') }}</VListSubheader>

        <VListItem
          v-for="item in palettes"
          :key="item.key"
          :value="item.key"
          @click="setPalette(item.key)"
        >
          <template #prepend>
            <!-- A swatch of the actual colour reads faster than its name. -->
            <span
              class="palette-swatch me-3"
              :style="{ backgroundColor: item.primary }"
            />
          </template>

          <VListItemTitle>{{ t(`palette.${item.key}`) }}</VListItemTitle>

          <template
            v-if="item.key === currentPalette.key"
            #append
          >
            <VIcon
              icon="bx-check"
              size="18"
            />
          </template>
        </VListItem>
      </VList>
    </VMenu>
  </IconBtn>
</template>

<style scoped>
.palette-swatch {
  display: inline-block;
  border-radius: 50%;
  block-size: 16px;
  inline-size: 16px;
}
</style>
