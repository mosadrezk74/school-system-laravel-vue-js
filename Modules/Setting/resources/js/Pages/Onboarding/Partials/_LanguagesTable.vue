<script setup>
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  rows: { type: Array, required: true },
  toggleRoute: { type: Function, required: true },
  defaultRoute: { type: Function, required: true },
})

const { t } = useI18n()

const enabledCount = computed(() => props.rows.filter(l => l.is_active).length)

const headers = computed(() => [
  { title: t('languages.name'), key: 'name' },
  { title: t('languages.locale'), key: 'locale' },
  { title: t('languages.direction'), key: 'direction' },
  { title: t('languages.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function toggle(language) {
  router.patch(props.toggleRoute(language), {}, { preserveScroll: true })
}

function makeDefault(language) {
  router.patch(props.defaultRoute(language), {}, { preserveScroll: true })
}
</script>

<template>
  <VAlert type="info" variant="tonal" density="compact" class="mb-4">
    {{ t('languages.hint', { count: enabledCount }) }}
  </VAlert>

  <VDataTable
    :headers="headers"
    :items="rows"
    :items-per-page="-1"
    hide-default-footer
  >
    <template #item.direction="{ item }">
      <VChip size="small" variant="tonal">
        {{ item.direction.toUpperCase() }}
      </VChip>
    </template>

    <template #item.status="{ item }">
      <VChip v-if="item.is_default" color="primary" size="small">
        {{ t('languages.default') }}
      </VChip>
      <VChip v-else-if="item.is_active" color="success" size="small">
        {{ t('languages.enabled') }}
      </VChip>
      <span v-else class="text-disabled">
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
</template>
