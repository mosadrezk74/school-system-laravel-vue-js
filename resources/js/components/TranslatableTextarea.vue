<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, required: true },
  field: { type: String, default: null },
  errors: { type: Object, default: () => ({}) },
  rows: { type: [String, Number], default: 4 },
  required: { type: Boolean, default: false },

  /**
   * Character limit, matching the `max:` in the matching Form Request.
   *
   * One prop drives both the hard stop and the counter so the two cannot
   * disagree. Long-form fields benefit most from the counter: without it the
   * only feedback on an over-length article is a rejected save.
   */
  maxlength: { type: [String, Number], default: null },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <VTextarea
    :model-value="modelValue"
    :label="`${label}${required ? ' *' : ''}`"
    :rows="rows"
    :error-messages="errors[field ?? label]"
    :maxlength="maxlength ?? undefined"
    :counter="maxlength ? Number(maxlength) : undefined"
    @update:model-value="value => $emit('update:modelValue', value)"
  />
</template>
