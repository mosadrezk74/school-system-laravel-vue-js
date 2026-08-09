<script setup>
/**
 * The paginator footer, previously copy-pasted into 41 index pages.
 *
 * Takes Laravel's paginator `meta` object as-is so no controller or response
 * key changes: `{ current_page, last_page, from, to, total }`.
 */
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useDisplay } from 'vuetify'

const props = defineProps({
  meta: { type: Object, default: null },
})

defineEmits(['change'])

const { t } = useI18n()
const { smAndDown } = useDisplay()

/** A single page needs no controls — the row count alone is the whole story. */
const hasPages = computed(() => (props.meta?.last_page ?? 1) > 1)

/*
 * Fewer page buttons on a phone: the default 5 plus first/last/prev/next
 * overflows a 360px viewport and was a source of horizontal scroll.
 */
const totalVisible = computed(() => (smAndDown.value ? 3 : 5))
</script>

<template>
  <template v-if="meta && (hasPages || meta.total)">
    <VDivider />

    <div class="app-pagination">
      <span class="text-body-2 text-medium-emphasis app-numeric">
        {{ t('common.showingResults', { from: meta.from ?? 0, to: meta.to ?? 0, total: meta.total ?? 0 }) }}
      </span>

      <VPagination
        v-if="hasPages"
        :model-value="meta.current_page"
        :length="meta.last_page"
        :total-visible="totalVisible"
        density="comfortable"
        @update:model-value="$emit('change', $event)"
      />
    </div>
  </template>
</template>

<style lang="scss" scoped>
.app-pagination {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  padding: var(--app-space-4);
  gap: var(--app-space-3);
}
</style>
