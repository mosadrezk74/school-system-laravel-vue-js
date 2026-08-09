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
      <VIcon icon="bx-layer" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('gradeLevels.sections.details') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('gradeLevels.sections.detailsDesc') }}
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
    field="name"
    :label="t('gradeLevels.fields.name')"
    :errors="form.errors"
    required
    class="mb-4"
  />

  <TranslatableTextarea
    v-model="form.description"
    field="description"
    :label="t('gradeLevels.fields.description')"
    :errors="form.errors"
    class="mb-4"
  />

  <VRow>
    <VCol cols="12" md="6">
      <VTextField
        v-model="form.display_order"
        type="number"
        min="0"
        :label="t('gradeLevels.fields.displayOrder')"
        :hint="t('gradeLevels.hints.displayOrder')"
        persistent-hint
        :error-messages="form.errors.display_order"
      />
    </VCol>

    <VCol cols="12" md="6" class="d-flex flex-column justify-center">
      <VSwitch
        v-model="form.is_active"
        :label="t('gradeLevels.fields.isActive')"
      />
      <p class="text-caption text-medium-emphasis mb-0">
        {{ t('gradeLevels.hints.isActive') }}
      </p>
    </VCol>
  </VRow>
</template>

<style scoped>
/*
 * Hints here double as help text, so they must read as secondary. _field.scss
 * colours .v-messages with the theme colour while focused, so `color` is
 * forced back to neutral; :not(.v-input--error) keeps real validation
 * messages in the error colour.
 */
:deep(.v-input:not(.v-input--error) .v-messages) {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.75rem;
  opacity: var(--v-medium-emphasis-opacity);
}
</style>
