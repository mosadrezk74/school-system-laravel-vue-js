<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  studyResource: { type: Object, required: true },
})

const { t } = useI18n()

function formatFileSize(bytes) {
  if (!bytes)
    return '—'

  const units = ['B', 'KB', 'MB', 'GB']
  let value = bytes
  let unit = 0

  while (value >= 1024 && unit < units.length - 1) {
    value /= 1024
    unit += 1
  }

  return `${value.toFixed(unit === 0 ? 0 : 1)} ${units[unit]}`
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString() : '—'
}
</script>

<template>
  <Head :title="studyResource.title" />

  <VRow>
    <!-- 👉 Header banner -->
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar color="primary" variant="tonal" size="56">
            <VIcon icon="bx-folder-open" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ studyResource.title }}
              </h5>
              <VChip color="primary" variant="tonal" size="small">
                {{ studyResource.type_label ?? '—' }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ studyResource.subject?.name ?? '—' }}
              · {{ studyResource.grade_level?.name ?? '—' }}
              · {{ studyResource.teacher?.name ?? '—' }}
            </p>
          </div>

          <VBtn
            v-if="studyResource.has_file"
            prepend-icon="bx-download"
            :href="`/academic/study-resources/${studyResource.id}/download`"
          >
            {{ t('studyResources.download') }}
          </VBtn>

          <VBtn
            v-if="studyResource.external_url"
            variant="tonal"
            prepend-icon="bx-link-external"
            :href="studyResource.external_url"
            target="_blank"
            rel="noopener noreferrer"
          >
            {{ t('studyResources.openLink') }}
          </VBtn>

          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="bx-edit"
            :href="`/academic/study-resources/${studyResource.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Details -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-detail" />
          </template>
          <VCardTitle>{{ t('studyResources.sections.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('studyResources.description') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ studyResource.description ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
            <VRow no-gutters>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('studyResources.subject') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ studyResource.subject?.name ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('studyResources.gradeLevel') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ studyResource.grade_level?.name ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('studyResources.classroom') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ studyResource.classroom?.name ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('studyResources.teacher') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ studyResource.teacher?.name ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
            </VRow>
          </VList>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 File -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-paperclip" />
          </template>
          <VCardTitle>{{ t('studyResources.sections.file') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VRow no-gutters>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('studyResources.fileName') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ studyResource.file_name ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('studyResources.fileType') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ studyResource.file_mime_type ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('studyResources.fileSize') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ formatFileSize(studyResource.file_size) }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('studyResources.uploadedAt') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ formatDate(studyResource.uploaded_at) }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('studyResources.downloadsCount') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ studyResource.downloads_count }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('studyResources.externalUrl') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ studyResource.external_url ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
            </VRow>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
