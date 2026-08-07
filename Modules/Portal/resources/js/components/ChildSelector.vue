<script setup>
import { router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  children: { type: Array, default: () => [] },
  selected: { type: [Number, null], default: null },
  url: { type: String, required: true },
})

const { t } = useI18n()

const child = ref(props.selected)

watch(child, value => {
  router.get(props.url, value ? { child: value } : {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
})

const options = computed(() => props.children.map(item => ({
  title: item.name,
  value: item.id,
})))
</script>

<template>
  <VSelect
    v-if="options.length > 1"
    v-model="child"
    :items="options"
    :label="t('portal.parent.selectChild')"
    density="compact"
    hide-details
    style="min-inline-size: 220px;"
  />
</template>
