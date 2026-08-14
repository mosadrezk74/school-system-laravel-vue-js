<script setup>
/**
 * One attachment inside a sent message.
 *
 * Images render a thumbnail from the module's authorised preview route;
 * everything else gets an icon, a name and a size, because handing an
 * arbitrary file to the browser to render is not something the UI decides —
 * the server publishes `is_previewable` and this only obeys it.
 */
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  attachment: { type: Object, required: true },
})

const { t } = useI18n()

const lightbox = ref(false)
const thumbnailFailed = ref(false)

const showThumbnail = computed(() => props.attachment.is_previewable && !thumbnailFailed.value)

/** Icon by family, so a spreadsheet does not look like a slide deck. */
const icon = computed(() => {
  const mime = props.attachment.mime_type ?? ''

  if (mime.startsWith('image/'))
    return 'bx-image'
  if (mime === 'application/pdf')
    return 'bx-file-blank'
  if (mime.includes('word'))
    return 'bx-file-doc'
  if (mime.includes('sheet') || mime.includes('excel') || mime === 'text/csv')
    return 'bx-spreadsheet'
  if (mime.includes('presentation') || mime.includes('powerpoint'))
    return 'bx-slideshow'
  if (mime.includes('zip'))
    return 'bx-archive'

  return 'bx-file'
})

/**
 * Sizes are shown in the active locale's numerals, which matters in Arabic.
 * Bytes are rounded to one decimal from KB up; below that the exact count is
 * shorter than any rounding of it.
 */
const readableSize = computed(() => {
  const bytes = Number(props.attachment.size ?? 0)

  if (!Number.isFinite(bytes) || bytes <= 0)
    return null

  const units = ['B', 'KB', 'MB', 'GB']
  let value = bytes
  let unit = 0

  while (value >= 1024 && unit < units.length - 1) {
    value /= 1024
    unit += 1
  }

  return `${unit === 0 ? value : value.toFixed(1)} ${t(`communication.chat.units.${units[unit]}`)}`
})
</script>

<template>
  <div class="chat-attachment">
    <!-- Image: thumbnail that opens a lightbox. -->
    <button
      v-if="showThumbnail"
      type="button"
      class="chat-attachment__thumb"
      :aria-label="t('communication.chat.previewImage', { name: attachment.name })"
      @click="lightbox = true"
    >
      <img
        :src="attachment.preview_url"
        :alt="attachment.name"
        loading="lazy"
        @error="thumbnailFailed = true"
      >
    </button>

    <!-- Everything else: icon, name, size, download. -->
    <div
      v-else
      class="chat-attachment__file"
    >
      <VAvatar
        size="36"
        rounded="lg"
        color="primary"
        variant="tonal"
      >
        <VIcon
          :icon="icon"
          size="20"
        />
      </VAvatar>

      <div class="chat-attachment__meta">
        <span class="chat-attachment__name">{{ attachment.name }}</span>
        <span
          v-if="readableSize"
          class="text-caption text-medium-emphasis app-numeric"
        >{{ readableSize }}</span>
      </div>

      <VBtn
        :href="attachment.download_url"
        icon="bx-download"
        variant="text"
        size="small"
        density="comfortable"
        :aria-label="t('communication.chat.download')"
      />
    </div>

    <VDialog
      v-model="lightbox"
      max-width="900"
    >
      <VCard>
        <VCardItem>
          <VCardTitle class="text-truncate">
            {{ attachment.name }}
          </VCardTitle>
          <template #append>
            <VBtn
              icon="bx-x"
              variant="text"
              density="comfortable"
              :aria-label="t('common.close')"
              @click="lightbox = false"
            />
          </template>
        </VCardItem>

        <VCardText class="text-center">
          <img
            :src="attachment.preview_url"
            :alt="attachment.name"
            class="chat-attachment__full"
          >
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            :href="attachment.download_url"
            prepend-icon="bx-download"
            variant="tonal"
          >
            {{ t('communication.chat.download') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style lang="scss" scoped>
.chat-attachment {
  max-inline-size: 100%;
}

.chat-attachment__thumb {
  display: block;
  padding: 0;
  border: none;
  border-radius: 8px;
  background: none;
  cursor: pointer;
  inline-size: fit-content;
  overflow: hidden;

  img {
    display: block;
    max-block-size: 220px;
    max-inline-size: 260px;
    object-fit: cover;
  }
}

.chat-attachment__file {
  display: flex;
  align-items: center;
  padding: var(--app-space-2);
  border: 1px solid rgba(var(--v-border-color), 0.16);
  border-radius: 8px;
  gap: var(--app-space-3);
  max-inline-size: 320px;
}

.chat-attachment__meta {
  display: flex;
  flex-direction: column;
  min-inline-size: 0;
}

.chat-attachment__name {
  font-size: 0.875rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chat-attachment__full {
  max-inline-size: 100%;
  max-block-size: 70vh;
  object-fit: contain;
}
</style>
