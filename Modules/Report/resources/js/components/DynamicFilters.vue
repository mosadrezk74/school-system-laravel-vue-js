<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  filters: { type: Array, default: () => [] },
  modelValue: { type: Object, default: () => ({}) },
  errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:modelValue'])

const { t } = useI18n()

const booleanOptions = computed(() => [
  { title: t('report.yes'), value: true },
  { title: t('report.no'), value: false },
])

function valueOf(key) {
  return props.modelValue[key] ?? null
}

function setValue(key, value) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}

function optionsFor(filter) {
  return (filter.options ?? []).map(option => ({
    title: option.label,
    value: option.value,
  }))
}

function errorFor(key) {
  return props.errors[`filters.${key}`]
}
</script>

<template>
  <VRow>
    <VCol
      v-for="filter in filters"
      :key="filter.key"
      cols="12"
      sm="6"
      md="3"
    >
      <VSelect
        v-if="filter.type === 'select'"
        :model-value="valueOf(filter.key)"
        :items="optionsFor(filter)"
        :label="filter.label"
        density="compact"
        clearable
        :error-messages="errorFor(filter.key)"
        @update:model-value="value => setValue(filter.key, value)"
      />

      <VSelect
        v-else-if="filter.type === 'boolean'"
        :model-value="valueOf(filter.key)"
        :items="booleanOptions"
        :label="filter.label"
        density="compact"
        clearable
        :error-messages="errorFor(filter.key)"
        @update:model-value="value => setValue(filter.key, value)"
      />

      <VTextField
        v-else
        :model-value="valueOf(filter.key)"
        :type="filter.type === 'number' ? 'number' : filter.type === 'date' ? 'date' : 'text'"
        :label="filter.label"
        density="compact"
        clearable
        :error-messages="errorFor(filter.key)"
        @update:model-value="value => setValue(filter.key, value)"
      />
    </VCol>
  </VRow>
</template>
