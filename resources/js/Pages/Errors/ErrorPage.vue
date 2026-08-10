<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

/*
 * Rendered by the exception handler for every failed web request, so it must
 * stand on its own: a guest hitting a 404 has none of the shell's props.
 * `layout: null` below keeps the sidebar out for exactly that reason.
 */
const props = defineProps({
  status: { type: Number, required: true },
  statusKey: { type: String, required: true },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
})

const { t, te } = useI18n()
const page = usePage()

/*
 * The server already resolved the copy in the request locale, which is what a
 * cold error page needs. The client bundle is preferred when it has the key,
 * so switching language in the SPA re-renders without a round trip.
 */
const resolve = (key, fallback) => {
  const full = `errors.status.${props.statusKey}.${key}`

  return te(full) ? t(full) : fallback
}

const heading = computed(() => resolve('title', props.title))
const explanation = computed(() => resolve('description', props.description))

const homeHref = computed(() => page.props.auth?.portal ?? '/dashboard')

const goBack = () => window.history.back()
</script>

<template>
  <div class="error-page d-flex flex-column align-center justify-center text-center">
    <Head :title="`${props.status} — ${heading}`" />

    <h1 class="error-code font-weight-bold">
      {{ props.status }}
    </h1>

    <h2 class="text-h4 font-weight-medium mb-2">
      {{ heading }}
    </h2>

    <p class="text-body-1 text-medium-emphasis error-description mb-8">
      {{ explanation }}
    </p>

    <div class="d-flex flex-wrap justify-center gap-4">
      <VBtn
        :href="homeHref"
        :as="Link"
        prepend-icon="bx-home-smile"
      >
        {{ te('errors.actions.home') ? t('errors.actions.home') : 'Home' }}
      </VBtn>

      <VBtn
        variant="tonal"
        prepend-icon="bx-arrow-back"
        @click="goBack"
      >
        {{ te('errors.actions.back') ? t('errors.actions.back') : 'Back' }}
      </VBtn>
    </div>
  </div>
</template>

<script>
export default { layout: null }
</script>

<style scoped>
.error-page {
  padding: 2rem 1.5rem;
  min-block-size: 100dvh;
}

.error-code {
  color: rgb(var(--v-theme-primary));
  font-size: clamp(5rem, 18vw, 9rem);
  line-height: 1;
  margin-block-end: 1rem;
}

.error-description {
  max-inline-size: 32rem;
}

/* The back arrow has a direction, so it has to follow the document's. */
:deep([dir="rtl"]) .bx-arrow-back,
[dir="rtl"] .bx-arrow-back {
  transform: scaleX(-1);
}
</style>
