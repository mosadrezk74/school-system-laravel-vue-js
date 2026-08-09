<script setup>
/**
 * One titled group of fields within a form.
 *
 * The brief asks for `Section title → description → fields → actions` instead
 * of one undifferentiated wall of inputs. On desktop the title column sits
 * beside the fields (the Stripe/Vercel settings layout, which lets someone
 * scan headings without reading every field); below `md` it stacks so fields
 * get the full width.
 */
defineProps({
  title: { type: String, required: true },
  description: { type: String, default: null },
})
</script>

<template>
  <section class="app-form-section">
    <div class="app-form-section__heading">
      <h2 class="text-h6 mb-0">
        {{ title }}
      </h2>
      <p
        v-if="description"
        class="text-body-2 text-medium-emphasis mb-0 mt-1"
      >
        {{ description }}
      </p>
    </div>

    <div class="app-form-section__fields">
      <slot />
    </div>
  </section>
</template>

<style lang="scss" scoped>
.app-form-section {
  display: grid;
  gap: var(--app-space-4) var(--app-space-8);
  grid-template-columns: 1fr;

  & + & {
    padding-block-start: var(--app-space-6);
    border-block-start: var(--app-border);
    margin-block-start: var(--app-space-6);
  }

  @media (min-width: 960px) {
    grid-template-columns: minmax(0, 15rem) minmax(0, 1fr);
  }

  &__heading {
    min-inline-size: 0;
  }

  &__fields {
    display: flex;
    flex-direction: column;
    gap: var(--app-space-4);
    min-inline-size: 0;
  }
}
</style>
