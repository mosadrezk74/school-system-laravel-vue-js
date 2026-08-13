<script setup>
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

defineProps({
  form: { type: Object, required: true },
  isEdit: { type: Boolean, default: false },
})

const { t } = useI18n()
</script>

<template>
  <div class="d-flex align-center gap-3 mb-6">
    <VAvatar color="primary" variant="tonal" size="40">
      <VIcon icon="bx-book-open" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('subject.sections.basic') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('subject.sections.details') }}
      </p>
    </div>
  </div>

  <!--
    One plain-text input per translatable field: the server detects which
    language was typed and fills in the other active locales, so there is no
    per-locale tab to keep in sync here.
  -->
  <TranslatableTextInput
    v-model="form.name"
    :maxlength="100"
    field="name"
    :label="t('subject.fields.name')"
    :errors="form.errors"
    required
    class="mb-4"
  />

  <TranslatableTextarea
    v-model="form.description"
    :maxlength="500"
    field="description"
    :label="t('subject.fields.description')"
    :errors="form.errors"
    class="mb-4"
  />

  <VSwitch
    v-model="form.is_active"
    :label="t('subject.fields.is_active')"
  />
  <p class="text-caption text-medium-emphasis mb-0">
    {{ t('subject.hints.is_active') }}
  </p>
</template>

<style scoped>
/*
 * Hints double as help text here, so they must read as secondary. The
 * template's _field.scss colours .v-messages with the theme colour while a
 * field is focused, so `color` is forced back to neutral;
 * :not(.v-input--error) keeps real validation messages in the error colour.
 */
:deep(.v-input:not(.v-input--error) .v-messages) {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.75rem;
  opacity: var(--v-medium-emphasis-opacity);
}
</style>
