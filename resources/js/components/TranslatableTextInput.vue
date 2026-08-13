<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, required: true },
  field: { type: String, default: null },
  errors: { type: Object, default: () => ({}) },
  required: { type: Boolean, default: false },

  /**
   * Character limit, matching the `max:` in the matching Form Request.
   *
   * One prop drives both the hard stop and the counter so the two cannot
   * disagree: a field that stops accepting input at 50 while the counter
   * promises 255 is worse than having neither.
   */
  maxlength: { type: [String, Number], default: null },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <VTextField
    :model-value="modelValue"
    :label="`${label}${required ? ' *' : ''}`"
    :error-messages="errors[field ?? label]"
    :maxlength="maxlength ?? undefined"
    :counter="maxlength ? Number(maxlength) : undefined"
    @update:model-value="value => $emit('update:modelValue', value)"
  />
</template>
