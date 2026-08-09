<script setup>
import NavItems from '@/layouts/components/NavItems.vue'
import logo from '@images/logo.svg?raw'
import VerticalNavLayout from '@layouts/components/VerticalNavLayout.vue'
import Footer from '@/layouts/components/Footer.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import NavbarLanguageSwitcher from '@modules/Setting/resources/js/components/NavbarLanguageSwitcher.vue'
import NavbarPaletteSwitcher from '@/layouts/components/NavbarPaletteSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

/*
 * The sidebar contents are injected rather than hardcoded so each portal can
 * supply navigation designed for its role while sharing this entire shell.
 */
defineProps({
  navComponent: { type: Object, default: () => NavItems },
})

/*
 * Controllers flash messages with ->with('success', ...); the layout is the
 * one place that surfaces them, so no page has to handle its own toasts.
 */
const page = usePage()
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')

watch(() => page.props.flash, flash => {
  if (!flash) return

  const message = flash.success ?? flash.error
  if (!message) return

  snackbarText.value = message
  snackbarColor.value = flash.error ? 'error' : 'success'
  snackbar.value = true
}, { deep: true, immediate: true })
</script>

<template>
  <VerticalNavLayout>
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <!-- 👉 Vertical nav toggle in overlay mode -->
        <IconBtn
          class="ms-n3 d-lg-none"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon icon="bx-menu" />
        </IconBtn>

        <!--
          The page title lives in the navbar on mobile, where the sidebar is
          hidden and there is otherwise nothing telling the user where they
          are. On desktop the page's own AppPageHeader carries it, so this
          would be a duplicate.
        -->
        <span class="text-body-1 font-weight-medium text-truncate d-lg-none ms-2">
          {{ $page.props.name }}
        </span>

        <VSpacer />

        <NavbarPaletteSwitcher />

        <NavbarLanguageSwitcher />

        <NavbarThemeSwitcher class="me-1" />

        <UserProfile />
      </div>
    </template>

    <template #vertical-nav-header="{ toggleIsOverlayNavActive }">
      <Link
        href="/dashboard"
        class="app-logo app-title-wrapper"
      >
        <!-- eslint-disable vue/no-v-html -->
        <div
          class="d-flex"
          v-html="logo"
        />
        <!-- eslint-enable -->

        <h1 class="app-logo-title">
          {{ $page.props.name }}
        </h1>
      </Link>

      <IconBtn
        class="d-block d-lg-none"
        @click="toggleIsOverlayNavActive(false)"
      >
        <VIcon icon="bx-x" />
      </IconBtn>
    </template>

    <template #vertical-nav-content>
      <component :is="navComponent" />
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>
  </VerticalNavLayout>

  <VSnackbar
    v-model="snackbar"
    :color="snackbarColor"
    location="bottom end"
    :timeout="4000"
  >
    {{ snackbarText }}
  </VSnackbar>
</template>

<style lang="scss" scoped>
.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.75rem;

  .app-logo-title {
    font-size: 1.125rem;
    font-weight: 600;
    line-height: 1.5rem;

    /*
      Not uppercased: Arabic has no letter case, so `text-transform` shouted
      the English name while leaving the Arabic one untouched — the two
      locales rendered the same brand at different visual weights.
    */
  }
}
</style>
