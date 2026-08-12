<script setup>
/**
 * The school news feed.
 *
 * Declares no layout of its own: the same page serves every portal, so it
 * inherits whichever shell the signed-in user has — the pattern the
 * notifications and terms pages already use.
 *
 * Only live items the reader's audience covers reach this page; the server
 * decides that in NewsQuery, never the markup below.
 */
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  news: { type: Object, required: true },
})

const { t } = useI18n()

const items = computed(() => props.news.data ?? [])

const formatDate = value => (value ? new Date(value).toLocaleDateString() : '')

function goToPage(page) {
  router.get('/news', { page }, { preserveState: true, preserveScroll: true })
}
</script>

<template>
  <Head :title="t('news.feedTitle')" />

  <AppPageHeader :title="t('news.feedTitle')" />

  <AppEmptyState
    v-if="!items.length"
    :title="t('news.feedEmpty')"
    :description="t('news.feedEmptyHint')"
    icon="bx-news"
  />

  <template v-else>
    <VCard
      v-for="item in items"
      :key="item.id"
      class="mb-4"
    >
      <VCardItem>
        <template #prepend>
          <VIcon
            v-if="item.is_pinned"
            icon="bx-pin"
            color="primary"
          />
        </template>
        <VCardTitle>{{ item.title }}</VCardTitle>
        <VCardSubtitle>
          {{ formatDate(item.published_at) }}
          <span v-if="item.author"> · {{ item.author }}</span>
        </VCardSubtitle>
      </VCardItem>

      <VCardText>
        <!--
          Plain text with newlines preserved. The stored body is author-entered
          prose, never markup, so it is never rendered as HTML.
        -->
        <div class="news-body text-body-1">
          {{ item.body }}
        </div>
      </VCardText>
    </VCard>

    <AppPagination
      :meta="news.meta"
      @change="goToPage"
    />
  </template>
</template>

<style scoped>
.news-body {
  white-space: pre-wrap;
  overflow-wrap: anywhere;
}
</style>
