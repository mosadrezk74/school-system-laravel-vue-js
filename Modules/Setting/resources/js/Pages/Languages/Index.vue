<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguagesTable from '../Onboarding/Partials/_LanguagesTable.vue'

const props = defineProps({
  // Not `languages`: that name is taken by the shared prop holding the enabled
  // list. A collection resource arrives as { data: [...] }.
  rows: { type: Object, required: true },
})

const { t } = useI18n()

const items = computed(() => props.rows.data ?? [])
</script>

<template>
  <Head :title="t('languages.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('languages.title') }}</VCardTitle>
      <VCardSubtitle class="mt-1">
        {{ t('languages.subtitle') }}
      </VCardSubtitle>
    </VCardItem>

    <VCardText>
      <LanguagesTable
        :rows="items"
        :toggle-route="language => `/settings/languages/${language.id}/toggle`"
        :default-route="language => `/settings/languages/${language.id}/default`"
      />
    </VCardText>
  </VCard>
</template>
