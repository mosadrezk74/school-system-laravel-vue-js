<script setup>
const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, required: true },
  field: { type: String, default: null },
  errors: { type: Object, default: () => ({}) },
  rows: { type: [String, Number], default: 4 },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <VTextarea
    :model-value="modelValue"
    :label="label"
    :rows="rows"
    :error-messages="errors[field ?? label]"
    @update:model-value="value => $emit('update:modelValue', value)"
  />
</template>
