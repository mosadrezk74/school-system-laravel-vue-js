<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  gradeLevel: { type: Object, required: true },
})

const { t } = useI18n()
const page = usePage()

// The enabled languages are shared globally, so the stored translations are
// listed in the school's own language order rather than JSON key order.
const languages = computed(() => page.props.languages ?? [])

const nameTranslations = computed(() => languages.value
  .map(language => ({ language, value: props.gradeLevel.name_translations?.[language.locale] }))
  .filter(entry => entry.value))

const descriptionTranslations = computed(() => languages.value
  .map(language => ({ language, value: props.gradeLevel.description_translations?.[language.locale] }))
  .filter(entry => entry.value))
</script>

<template>
  <Head :title="gradeLevel.name" />

  <VCard>
    <VCardItem>
      <VCardTitle class="d-flex align-center gap-2">
        {{ gradeLevel.name }}
        <VChip :color="gradeLevel.is_active ? 'success' : 'secondary'" size="small">
          {{ gradeLevel.is_active ? t('common.active') : t('common.inactive') }}
        </VChip>
      </VCardTitle>
      <VCardSubtitle>
        {{ t('gradeLevels.fields.displayOrder') }}: {{ gradeLevel.display_order }}
      </VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-edit"
          :href="`/academic/grade-levels/${gradeLevel.id}/edit`"
          :as="Link"
          color="primary"
          variant="tonal"
        >
          {{ t('common.edit') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDivider />

    <VCardText>
      <h6 class="text-h6 mb-3">
        {{ t('gradeLevels.fields.name') }}
      </h6>
      <VRow no-gutters class="mb-4">
        <VCol
          v-for="entry in nameTranslations"
          :key="entry.language.locale"
          cols="12"
          md="6"
        >
          <VListItem>
            <VListItemTitle class="text-medium-emphasis text-caption">
              {{ entry.language.name }}
            </VListItemTitle>
            <VListItemSubtitle class="text-body-1 text-high-emphasis">
              {{ entry.value }}
            </VListItemSubtitle>
          </VListItem>
        </VCol>
      </VRow>

      <template v-if="descriptionTranslations.length">
        <VDivider class="mb-4" />

        <h6 class="text-h6 mb-3">
          {{ t('gradeLevels.fields.description') }}
        </h6>
        <VRow no-gutters>
          <VCol
            v-for="entry in descriptionTranslations"
            :key="entry.language.locale"
            cols="12"
            md="6"
          >
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ entry.language.name }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ entry.value }}
              </VListItemSubtitle>
            </VListItem>
          </VCol>
        </VRow>
      </template>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end">
      <VBtn
        variant="tonal"
        color="secondary"
        href="/academic/grade-levels"
        :as="Link"
      >
        {{ t('common.back') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
