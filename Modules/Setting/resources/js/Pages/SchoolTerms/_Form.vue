<script setup>
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

defineProps({
  form: { type: Object, required: true },
})

const { t } = useI18n()
</script>

<template>
  <div class="d-flex align-center gap-3 mb-6">
    <VAvatar
      color="primary"
      variant="tonal"
      size="40"
    >
      <VIcon icon="bx-file" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('schoolTerms.sections.details') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('schoolTerms.sections.detailsDesc') }}
      </p>
    </div>
  </div>

  <!--
    One plain-text input per translatable field: the server detects which
    language was typed and fills in the other active locales, so there is no
    per-locale tab to keep in sync here.
  -->
  <TranslatableTextInput
    v-model="form.title"
    :maxlength="200"
    field="title"
    :label="t('schoolTerms.fields.title')"
    :errors="form.errors"
    required
    class="mb-4"
  />

  <TranslatableTextarea
    v-model="form.content"
    :maxlength="50000"
    field="content"
    :label="t('schoolTerms.fields.content')"
    :errors="form.errors"
    :rows="16"
    class="mb-2"
  />
</template>
