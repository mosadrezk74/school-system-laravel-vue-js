<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  modelValue: { type: Object, required: true },
  label: { type: String, required: true },
  field: { type: String, default: null },
  errors: { type: Object, default: () => ({}) },
  required: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const page = usePage()
const languages = computed(() => page.props.languages ?? [])

function errorFor(locale) {
  const key = props.field ?? props.label

  return props.errors[`${key}.${locale}`] ?? props.errors[locale]
}

function update(locale, value) {
  emit('update:modelValue', { ...props.modelValue, [locale]: value })
}
</script>

<template>
  <VRow>
    <VCol
      v-for="language in languages"
      :key="language.locale"
      cols="12"
      :md="Math.max(4, Math.floor(12 / languages.length))"
    >
      <VTextField
        :model-value="modelValue[language.locale]"
        :label="`${label} (${language.name})${required ? ' *' : ''}`"
        :error-messages="errorFor(language.locale)"
        :dir="language.direction"
        @update:model-value="value => update(language.locale, value)"
      />
    </VCol>
  </VRow>
</template>
