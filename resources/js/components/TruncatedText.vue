<script setup>
/**
 * A table cell's text, capped at a readable length with the full value on
 * hover.
 *
 * Long free-text columns — a qualification, an address, a note — stretch their
 * column until the rest of the row is squeezed into an unreadable ribbon. This
 * caps the visible text and hands the full string to a tooltip, so the table
 * keeps its column rhythm and nothing becomes unreachable.
 *
 * The tooltip is attached only when the text is actually cut. A tooltip that
 * repeats what is already fully visible is noise, and it would otherwise fire
 * on every short cell in the table.
 */
import { computed } from 'vue'

const props = defineProps({
  /**
   * The value to display. Non-strings are coerced, so a cell can pass a
   * number or a null straight from the API without guarding first.
   */
  text: { type: [String, Number, null], default: null },

  /** Visible length before truncation, in user-perceived characters. */
  length: { type: Number, default: 30 },

  /** Shown when `text` is null, undefined or blank. */
  placeholder: { type: String, default: '—' },
})

const normalized = computed(() => {
  if (props.text === null || props.text === undefined) {
    return ''
  }

  return String(props.text).trim()
})

const isBlank = computed(() => normalized.value === '')

/**
 * Split into grapheme clusters rather than UTF-16 code units.
 *
 * `String.prototype.slice` counts code units, which cuts an Arabic string
 * mid-cluster and can strand a combining mark or split an emoji into broken
 * halves. `Intl.Segmenter` counts what a reader would call a character, so a
 * 30-character cap means the same thing in Arabic and English.
 */
const graphemes = computed(() => {
  if (isBlank.value) {
    return []
  }

  if (typeof Intl === 'undefined' || typeof Intl.Segmenter !== 'function') {
    return [...normalized.value]
  }

  const segmenter = new Intl.Segmenter(undefined, { granularity: 'grapheme' })

  return Array.from(segmenter.segment(normalized.value), segment => segment.segment)
})

const isTruncated = computed(() => graphemes.value.length > props.length)

const displayText = computed(() => {
  if (isBlank.value) {
    return props.placeholder
  }

  if (!isTruncated.value) {
    return normalized.value
  }

  /* Trailing whitespace before the ellipsis reads as a gap, so trim the cut. */
  return `${graphemes.value.slice(0, props.length).join('').trimEnd()}...`
})
</script>

<template>
  <span
    v-if="isTruncated"
    class="app-truncated-text"
    tabindex="0"
  >
    {{ displayText }}

    <!--
      These two are pixels, and must stay pixels.

      Vuetify reads them with `parseFloat`, which keeps the number and throws
      the unit away: `max-width="24rem"` reached the overlay as `max-width:24px`
      — about one character wide. The full value then wrapped at every character
      and the tooltip rendered as a vertical ribbon of single letters running
      down the page. Any unit here is silently reinterpreted as px, so a bare
      number is the only honest way to write it.

      `min-width` is the floor of the same clamp
      (`clamp(availableWidth, minWidth, maxWidth)`), which keeps the tooltip
      readable when it opens near a viewport edge and little width is going.
    -->
    <VTooltip
      activator="parent"
      location="top"
      open-on-focus
      :min-width="240"
      :max-width="384"
    >
      <span class="app-truncated-text__full">{{ normalized }}</span>
    </VTooltip>
  </span>

  <span v-else>{{ displayText }}</span>
</template>

<style lang="scss" scoped>
.app-truncated-text {
  /*
    The text is already cut to length in script, so no clipping is needed here
    — this only marks the cell as interactive. The string is deliberately left
    wrappable: on a narrow mobile card, 30 characters can still exceed the
    value column, and wrapping is better than pushing the card sideways.
  */
  cursor: help;
}

.app-truncated-text__full {
  /*
    The tooltip is the only place the full value exists, so it must be allowed
    to wrap onto as many lines as it needs rather than overflowing its box.

    `break-word`, not `anywhere`: an unbroken string like an email address has
    no break opportunity, and without this it would overflow the tooltip
    instead of wrapping inside it. `anywhere` would also drag the element's
    min-content size down to a single character, which is not wanted even
    though the width now comes from the tooltip's own max-width.
  */
  display: block;
  white-space: pre-wrap;
  overflow-wrap: break-word;
}
</style>
