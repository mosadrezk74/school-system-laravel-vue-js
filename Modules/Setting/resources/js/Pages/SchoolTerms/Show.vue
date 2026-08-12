<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  term: { type: Object, required: true },
})

const { t } = useI18n()

const formatDate = value => (value ? new Date(value).toLocaleString() : '—')
</script>

<template>
  <Head :title="t('schoolTerms.versionLabel', { version: term.version })" />

  <AppPageHeader
    :title="term.title"
    :subtitle="t('schoolTerms.versionLabel', { version: term.version })"
  >
    <template #actions>
      <VBtn
        variant="tonal"
        color="secondary"
        href="/settings/school-terms"
        :as="Link"
      >
        {{ t('common.back') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VRow>
    <VCol
      cols="12"
      md="8"
    >
      <VCard>
        <VCardText>
          <!--
            Plain text, rendered with newlines preserved. The stored content
            is author-entered prose, never markup, so it is never treated as
            HTML.
          -->
          <div class="school-terms-content text-body-1">
            {{ term.content }}
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      cols="12"
      md="4"
    >
      <VCard>
        <VCardText>
          <VList lines="two">
            <VListItem :title="t('schoolTerms.fields.status')">
              <template #subtitle>
                <VChip
                  :color="term.status_color"
                  size="small"
                  class="mt-1"
                >
                  {{ t(`schoolTerms.statuses.${term.status}`) }}
                </VChip>
              </template>
            </VListItem>

            <VListItem
              :title="t('schoolTerms.fields.version')"
              :subtitle="`v${term.version}`"
            />

            <VListItem
              :title="t('schoolTerms.fields.publishedAt')"
              :subtitle="formatDate(term.published_at)"
            />

            <VListItem
              :title="t('schoolTerms.fields.acceptances')"
              :subtitle="String(term.acceptances_count ?? 0)"
            />

            <VListItem
              :title="t('schoolTerms.fields.createdBy')"
              :subtitle="term.created_by || '—'"
            />
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style scoped>
.school-terms-content {
  white-space: pre-wrap;
  overflow-wrap: anywhere;
}
</style>
