<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import NewsForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  item: { type: Object, required: true },
  statusOptions: { type: Array, default: () => [] },
  audienceOptions: { type: Array, default: () => [] },
})

const { t } = useI18n()

/** datetime-local needs `YYYY-MM-DDTHH:mm`, not the ISO string from the API. */
const toLocalInput = value => (value ? value.slice(0, 16) : null)

const form = useForm({
  title: props.item.title ?? '',
  body: props.item.body ?? '',
  audience: props.item.audience,
  status: props.item.status,
  published_at: toLocalInput(props.item.published_at),
  is_pinned: props.item.is_pinned,
})

function submit() {
  form.put(`/communication/news/${props.item.id}`)
}
</script>

<template>
  <Head :title="t('news.edit')" />

  <AppPageHeader
    :title="t('news.edit')"
    :subtitle="item.title"
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
        {{ t('common.update') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
