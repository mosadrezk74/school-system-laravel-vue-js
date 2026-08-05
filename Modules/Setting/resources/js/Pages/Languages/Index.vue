<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  // Not `languages`: that name is taken by the shared prop holding the enabled
  // list. A collection resource arrives as { data: [...] }.
  rows: { type: Object, required: true },
})

const { t } = useI18n()

const items = computed(() => props.rows.data ?? [])

const enabledCount = computed(() => items.value.filter(l => l.is_active).length)

const headers = computed(() => [
  { title: t('languages.name'), key: 'name' },
  { title: t('languages.locale'), key: 'locale' },
  { title: t('languages.direction'), key: 'direction' },
  { title: t('languages.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

/*
 * A full page visit (not preserveState) so the reload picks up the new
 * language list: the switcher and RTL both read it from shared props.
 */
const toggle = language => {
  router.patch(`/settings/languages/${language.id}/toggle`, {}, {
    preserveScroll: true,
  })
}

const makeDefault = language => {
  router.patch(`/settings/languages/${language.id}/default`, {}, {
    preserveScroll: true,
  })
}
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
      <VAlert
        type="info"
        variant="tonal"
        density="compact"
      >
        {{ t('languages.hint', { count: enabledCount }) }}
      </VAlert>
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="items"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.direction="{ item }">
        <VChip
          size="small"
          variant="tonal"
        >
          {{ item.direction.toUpperCase() }}
        </VChip>
      </template>

      <template #item.status="{ item }">
        <VChip
          v-if="item.is_default"
          color="primary"
          size="small"
        >
          {{ t('languages.default') }}
        </VChip>
        <VChip
          v-else-if="item.is_active"
          color="success"
          size="small"
        >
          {{ t('languages.enabled') }}
        </VChip>
        <span
          v-else
          class="text-disabled"
        >
          {{ t('languages.disabled') }}
        </span>
      </template>

      <template #item.actions="{ item }">
        <VBtn
          v-if="item.is_active && !item.is_default"
          variant="text"
          size="small"
          @click="makeDefault(item)"
        >
          {{ t('languages.makeDefault') }}
        </VBtn>

        <VSwitch
          :model-value="item.is_active"
          :disabled="item.is_default"
          color="primary"
          density="compact"
          hide-details
          class="d-inline-flex ms-2"
          @update:model-value="toggle(item)"
        />
      </template>
    </VDataTable>
  </VCard>
</template>
