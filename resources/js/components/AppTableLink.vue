<script setup>
/**
 * The clickable primary identifier in a data table row.
 *
 * Replaces the dedicated View icon button: the record's own name is the
 * natural thing to click, and it removes a column's worth of visual noise
 * from every table in the application.
 *
 * A real <Link>, not a span with a click handler, so middle-click, ctrl-click,
 * "open in new tab" and keyboard focus all behave the way a user expects.
 * Authorization is unchanged — this is navigation only; the Show route's own
 * policy still decides whether the page renders.
 */
import { Link } from '@inertiajs/vue3'

defineProps({
  /** Destination — the same URL the removed View button used. */
  href: { type: String, required: true },
})
</script>

<template>
  <Link
    :href="href"
    class="app-table-link"
  >
    <slot />
  </Link>
</template>

<style lang="scss" scoped>
.app-table-link {
  /*
    Reads as normal text until it is worth noticing: inheriting the row's
    colour keeps a dense table calm, and the weight bump is what marks this
    cell as the row's identity. The link only turns primary on interaction,
    which is the "subtle link appearance → clear hover feedback" the brief
    asks for, without a table full of blue underlined text.
  */
  color: inherit;
  font-weight: 500;
  text-decoration: none;
  transition: color 0.15s ease;

  &:hover {
    color: rgb(var(--v-theme-primary));
    text-decoration: underline;

    /* Offset keeps the rule clear of Arabic descenders, which sit lower than
       the Latin baseline and would otherwise be crossed through. */
    text-underline-offset: 0.2em;
  }

  /*
    Focus is inherited from the global `a:focus-visible` rule in
    styles.scss, so keyboard users get the same visible ring here as
    everywhere else in the app.
  */
}
</style>
