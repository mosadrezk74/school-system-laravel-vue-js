<script setup>
import { useI18n } from 'vue-i18n'

/*
 * The attribution was previously a hardcoded Arabic string, so an English,
 * French or German user saw "صُنع بـ بواسطة" in an otherwise translated
 * interface. It now goes through i18n like every other label.
 */
const { t } = useI18n()

const year = new Date().getFullYear()
</script>

<template>
  <div class="d-flex h-100 align-center justify-center justify-md-space-between flex-wrap gap-2">
    <span class="d-flex align-center text-body-2 text-medium-emphasis">
      <span class="app-numeric me-1">&copy; {{ year }}</span>
      {{ t('common.madeBy') }}
      <a
        href="https://m.athr-sa.com/"
        target="_blank"
        rel="noopener noreferrer"
        class="text-primary ms-1 font-weight-medium"
      >ATHR</a>
    </span>
  </div>
</template>
