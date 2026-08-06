<script setup>
const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, required: true },
  field: { type: String, default: null },
  errors: { type: Object, default: () => ({}) },
  required: { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <VTextField
    :model-value="modelValue"
    :label="`${label}${required ? ' *' : ''}`"
    :error-messages="errors[field ?? label]"
    @update:model-value="value => $emit('update:modelValue', value)"
  />
</template>
