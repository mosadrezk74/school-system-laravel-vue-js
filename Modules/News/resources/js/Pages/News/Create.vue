<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import NewsForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  statusOptions: { type: Array, default: () => [] },
  audienceOptions: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  title: '',
  body: '',
  audience: 'all',
  status: 'draft',
  published_at: null,
  is_pinned: false,
})

function submit() {
  form.post('/communication/news')
}
</script>

<template>
  <Head :title="t('news.addNew')" />

  <AppPageHeader
    :title="t('news.addNew')"
    :subtitle="t('news.subtitle')"
  />

  <VCard class="app-form-card">
    <VCardText>
      <form @submit.prevent="submit">
        <NewsForm
          :form="form"
          :status-options="statusOptions"
          :audience-options="audienceOptions"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/communication/news"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        :loading="form.processing"
        :disabled="form.processing"
        color="primary"
        @click="submit"
      >
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
