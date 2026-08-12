<script setup>
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

defineProps({
  form: { type: Object, required: true },
  statusOptions: { type: Array, default: () => [] },
  audienceOptions: { type: Array, default: () => [] },
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
      <VIcon icon="bx-news" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('news.sections.details') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('news.sections.detailsDesc') }}
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
    field="title"
    :label="t('news.fields.title')"
    :errors="form.errors"
    required
    class="mb-4"
  />

  <TranslatableTextarea
    v-model="form.body"
    field="body"
    :label="t('news.fields.body')"
    :errors="form.errors"
    :rows="12"
    class="mb-4"
  />

  <VRow>
    <VCol
      cols="12"
      md="4"
    >
      <VSelect
        v-model="form.audience"
        :items="audienceOptions"
        :label="t('news.fields.audience')"
        :hint="t('news.hints.audience')"
        persistent-hint
        :error-messages="form.errors.audience"
      />
    </VCol>

    <VCol
      cols="12"
      md="4"
    >
      <VSelect
        v-model="form.status"
        :items="statusOptions"
        :label="t('news.fields.status')"
        :error-messages="form.errors.status"
      />
    </VCol>

    <VCol
      cols="12"
      md="4"
    >
      <VTextField
        v-model="form.published_at"
        type="datetime-local"
        :label="t('news.fields.publishedAt')"
        :hint="t('news.hints.publishedAt')"
        persistent-hint
        :error-messages="form.errors.published_at"
      />
    </VCol>

    <VCol cols="12">
      <VSwitch
        v-model="form.is_pinned"
        :label="t('news.fields.isPinned')"
      />
      <p class="text-caption text-medium-emphasis mb-0">
        {{ t('news.hints.isPinned') }}
      </p>
    </VCol>
  </VRow>
</template>
