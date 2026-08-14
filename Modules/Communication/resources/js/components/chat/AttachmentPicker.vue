<script setup>
/**
 * Staged attachments, before the message is sent.
 *
 * Client-side checks here are UX only — they tell the user immediately why a
 * file will not do. The rule that actually decides is MessageAttachmentRule on
 * the server, which re-reads the file's real mime type; nothing rejected there
 * can be smuggled past by editing this.
 */
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  rules: { type: Object, default: () => ({ max_files: 10, max_size_kb: 10240, extensions: [], accept: '' }) },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const { t } = useI18n()

const input = ref(null)
const localError = ref(null)

const files = computed(() => props.modelValue ?? [])

const isFull = computed(() => files.value.length >= props.rules.max_files)

function open() {
  localError.value = null
  input.value?.click()
}

function onPicked(event) {
  const picked = Array.from(event.target.files ?? [])

  addFiles(picked)

  // Lets the same file be re-picked after being removed.
  event.target.value = ''
}

function addFiles(picked) {
  const accepted = []

  for (const file of picked) {
    if (files.value.length + accepted.length >= props.rules.max_files) {
      localError.value = t('communication.chat.errors.tooManyFiles', { max: props.rules.max_files })
      break
    }

    const extension = file.name.split('.').pop()?.toLowerCase() ?? ''

    if (props.rules.extensions.length && !props.rules.extensions.includes(extension)) {
      localError.value = t('communication.chat.errors.fileType', { name: file.name })
      continue
    }

    if (file.size > props.rules.max_size_kb * 1024) {
      localError.value = t('communication.chat.errors.fileTooLarge', { name: file.name, max: props.rules.max_size_kb })
      continue
    }

    accepted.push(file)
  }

  if (accepted.length)
    emit('update:modelValue', [...files.value, ...accepted])
}

function remove(index) {
  localError.value = null
  emit('update:modelValue', files.value.filter((_, position) => position !== index))
}

function readableSize(bytes) {
  if (!bytes)
    return ''

  const kb = bytes / 1024

  return kb < 1024
    ? `${Math.round(kb)} ${t('communication.chat.units.KB')}`
    : `${(kb / 1024).toFixed(1)} ${t('communication.chat.units.MB')}`
}

defineExpose({ open })
</script>

<template>
  <div class="attachment-picker">
    <input
      ref="input"
      type="file"
      multiple
      class="d-none"
      :accept="rules.accept"
      :disabled="disabled || isFull"
      @change="onPicked"
    >

    <VBtn
      icon="bx-paperclip"
      variant="text"
      density="comfortable"
      :disabled="disabled || isFull"
      :aria-label="t('communication.chat.attachFiles')"
      @click="open"
    />

    <div
      v-if="files.length"
      class="attachment-picker__staged"
    >
      <VChip
        v-for="(file, index) in files"
        :key="`${file.name}-${index}`"
        size="small"
        closable
        variant="tonal"
        :close-label="t('communication.chat.removeAttachment')"
        @click:close="remove(index)"
      >
        <VIcon
          icon="bx-file"
          size="14"
          start
        />
        <span class="attachment-picker__name">{{ file.name }}</span>
        <span class="text-caption text-medium-emphasis ms-1 app-numeric">{{ readableSize(file.size) }}</span>
      </VChip>
    </div>

    <p
      v-if="localError"
      class="text-caption text-error mb-0 mt-1"
      role="alert"
    >
      {{ localError }}
    </p>
  </div>
</template>

<style lang="scss" scoped>
.attachment-picker__staged {
  display: flex;
  flex-wrap: wrap;
  margin-block-start: var(--app-space-2);
  gap: var(--app-space-2);
}

.attachment-picker__name {
  display: inline-block;
  max-inline-size: 160px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  vertical-align: bottom;
}
</style>
