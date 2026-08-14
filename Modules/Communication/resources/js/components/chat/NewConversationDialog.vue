<script setup>
/**
 * Starting a conversation.
 *
 * The people offered here come from the server's `directory` prop, which is an
 * optional prop resolved only when this dialog asks for it. That list is
 * already narrowed to whoever the signed-in user is permitted to message, and
 * the same rule is re-applied when the form is submitted — so nothing is
 * decided by this component.
 */
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  directory: { type: Array, default: () => [] },
  types: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue'])

const { t } = useI18n()

const NAME_MAX = 120
const BODY_MAX = 5000

const search = ref('')
const loadingDirectory = ref(false)
let searchTimer = null

const form = useForm({
  type: 'direct',
  name: '',
  participant_ids: [],
  body: '',
})

const isGroup = computed(() => form.type === 'group')

const options = computed(() => props.directory.map(person => ({
  title: person.name,
  subtitle: t(`communication.chat.roles.${person.role}`),
  value: person.id,
})))

/**
 * VAutocomplete binds a bare value when `multiple` is false and an array when
 * it is true, but the server's contract is always `participant_ids: []`.
 *
 * Without this proxy the direct case wrote a scalar into `participant_ids`,
 * which made `.length` undefined — so the Start button never enabled, and had
 * it submitted, the `array` rule would have rejected it. Normalising here
 * keeps one shape leaving the component whichever mode the control is in.
 */
const selected = computed({
  get: () => (isGroup.value ? form.participant_ids : (form.participant_ids[0] ?? null)),
  set: value => {
    if (isGroup.value) {
      form.participant_ids = value ?? []

      return
    }

    form.participant_ids = (value === null || value === undefined) ? [] : [value]
  },
})

const canSubmit = computed(() => {
  if (form.processing || form.participant_ids.length === 0)
    return false

  return isGroup.value ? form.name.trim().length > 0 : form.participant_ids.length === 1
})

/** Pulls the directory on open, and again whenever the search term settles. */
function loadDirectory() {
  loadingDirectory.value = true

  router.reload({
    only: ['directory'],
    data: { directory_search: search.value || undefined },
    onFinish: () => {
      loadingDirectory.value = false
    },
  })
}

watch(() => props.modelValue, open => {
  if (open)
    loadDirectory()
})

watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(loadDirectory, 350)
})

/*
 * A direct thread has exactly one other person, so switching back from group
 * has to drop the extras rather than submit a shape the server will reject.
 */
watch(isGroup, group => {
  if (!group && form.participant_ids.length > 1)
    form.participant_ids = form.participant_ids.slice(0, 1)
})

function close() {
  emit('update:modelValue', false)
}

function submit() {
  if (!canSubmit.value)
    return

  form.post('/communication/chat/conversations', {
    onSuccess: () => {
      form.reset()
      close()
    },
  })
}
</script>

<template>
  <VDialog
    :model-value="modelValue"
    max-width="560"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <VCardItem>
        <VCardTitle>{{ t('communication.chat.newConversation') }}</VCardTitle>
      </VCardItem>

      <VCardText>
        <!--
          Full-width with equal, growing segments. Left to size themselves the
          buttons ran past the toggle's box and the two Arabic labels rendered
          on top of each other.
        -->
        <VBtnToggle
          v-model="form.type"
          mandatory
          divided
          variant="outlined"
          density="comfortable"
          class="conversation-type mb-4"
        >
          <VBtn
            v-for="type in types"
            :key="type.value"
            :value="type.value"
            class="flex-grow-1 text-none"
          >
            {{ type.title }}
          </VBtn>
        </VBtnToggle>

        <VTextField
          v-if="isGroup"
          v-model="form.name"
          :label="t('communication.chat.groupName')"
          :maxlength="NAME_MAX"
          :counter="NAME_MAX"
          :error-messages="form.errors.name"
          class="mb-4"
        />

        <VAutocomplete
          v-model="selected"
          v-model:search="search"
          :items="options"
          :label="isGroup ? t('communication.chat.participants') : t('communication.chat.recipient')"
          :multiple="isGroup"
          :loading="loadingDirectory"
          :error-messages="form.errors.participant_ids"
          :no-data-text="loadingDirectory ? t('common.loading') : t('communication.chat.noEligibleUsers')"
          item-title="title"
          item-value="value"
          chips
          closable-chips
          class="mb-4"
        >
          <template #item="{ props: itemProps, item }">
            <VListItem
              v-bind="itemProps"
              :subtitle="item.raw.subtitle"
            />
          </template>
        </VAutocomplete>

        <VTextarea
          v-model="form.body"
          :label="t('communication.chat.firstMessage')"
          :maxlength="BODY_MAX"
          :error-messages="form.errors.body"
          rows="3"
          auto-grow
          max-rows="6"
        />
      </VCardText>

      <VCardActions>
        <!--
          Says why Start is unavailable. A silently disabled button was exactly
          the failure this dialog shipped with, and a user has no way to tell a
          deliberate block from a broken one.
        -->
        <span
          v-if="!canSubmit && !form.processing"
          class="text-caption text-medium-emphasis px-2"
        >
          {{ form.participant_ids.length === 0
            ? t('communication.chat.hints.pickRecipient')
            : t('communication.chat.hints.nameGroup') }}
        </span>

        <VSpacer />

        <VBtn
          variant="tonal"
          color="secondary"
          @click="close"
        >
          {{ t('common.cancel') }}
        </VBtn>
        <VBtn
          color="primary"
          variant="flat"
          :disabled="!canSubmit"
          :loading="form.processing"
          @click="submit"
        >
          {{ t('communication.chat.start') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
/*
 * VBtnToggle sizes to its content by default, so two long Arabic labels
 * overflowed the dialog and painted over one another. Making it a full-width
 * flex row with equal segments keeps both labels inside their own button at
 * any string length.
 */
.conversation-type {
  display: flex;
  block-size: auto;
  inline-size: 100%;

  :deep(.v-btn) {
    flex: 1 1 0;
    min-inline-size: 0;
    padding-block: var(--app-space-2);
  }
}
</style>
