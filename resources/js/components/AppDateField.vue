<script setup>
import { computed, ref } from 'vue'
import { useDisplay } from 'vuetify'
import { useI18n } from 'vue-i18n'

/*
 * Replaces the browser's native <input type="date">, whose calendar is
 * unstyled, locale-inconsistent across browsers, and unusable on some mobile
 * webviews. Wraps Vuetify's VDatePicker so dates look and behave the same
 * everywhere and inherit the app's theme, palette and RTL direction.
 *
 * The v-model contract is deliberately identical to the native field it
 * replaces: an ISO `YYYY-MM-DD` string, which is what every form and Laravel
 * request in this project already sends and validates.
 */
const props = defineProps({
  modelValue: { type: String, default: null },
  label: { type: String, default: undefined },
  hint: { type: String, default: undefined },
  persistentHint: { type: Boolean, default: false },
  errorMessages: { type: [String, Array], default: () => [] },
  min: { type: String, default: undefined },
  max: { type: String, default: undefined },
  /*
   * Declared so the existing `required` attribute on migrated call sites stays
   * a prop instead of leaking into $attrs as a DOM attribute. It intentionally
   * does not decorate the label: forms already mark required fields by passing
   * the label through their local `required()` helper, and adding a second
   * asterisk here would render "Start Date * *".
   */
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
  clearable: { type: Boolean, default: true },
  density: { type: String, default: undefined },
  hideDetails: { type: [Boolean, String], default: undefined },
})

/*
 * Anything not declared above (style, class, placeholder…) belongs on the
 * visible text field rather than on the VMenu wrapper, which renders no DOM
 * of its own — so inheritance is disabled and $attrs is bound explicitly.
 */
defineOptions({ inheritAttrs: false })

const emit = defineEmits(['update:modelValue'])

const { t, locale } = useI18n()
const { smAndDown } = useDisplay()

const menuOpen = ref(false)

/**
 * Parses `YYYY-MM-DD` by hand rather than `new Date(string)`. Passing a bare
 * date string to the Date constructor parses it as UTC midnight, which in any
 * timezone behind UTC renders as the previous day.
 */
function toDate(value) {
  if (!value)
    return null

  const [year, month, day] = String(value).split('-').map(Number)

  if (!year || !month || !day)
    return null

  const date = new Date(year, month - 1, day)

  return Number.isNaN(date.getTime()) ? null : date
}

/** Formats from local date parts for the same timezone reason as toDate(). */
function toIso(date) {
  if (!date)
    return null

  const pad = number => String(number).padStart(2, '0')

  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
}

const selectedDate = computed({
  get: () => toDate(props.modelValue),
  set: value => emit('update:modelValue', toIso(Array.isArray(value) ? value[0] : value)),
})

const minDate = computed(() => toDate(props.min))
const maxDate = computed(() => toDate(props.max))

/*
 * The text shown in the closed field. Formatted in the active locale so an
 * Arabic UI reads as an Arabic date, while the value emitted upward stays the
 * ISO string the backend expects.
 */
const displayValue = computed(() => {
  const date = toDate(props.modelValue)

  if (!date)
    return ''

  return new Intl.DateTimeFormat(locale.value, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(date)
})

function select(value) {
  selectedDate.value = value
  menuOpen.value = false
}

function clear() {
  emit('update:modelValue', null)
  menuOpen.value = false
}

function selectToday() {
  select(new Date())
}
</script>

<template>
  <!--
    On phones the calendar opens as a centred dialog: a VMenu anchored to the
    field would otherwise be clipped by the viewport or covered by the virtual
    keyboard. On larger screens it stays an inline dropdown next to the field.
  -->
  <VMenu
    v-model="menuOpen"
    :close-on-content-click="false"
    :fullscreen="false"
    :location="smAndDown ? 'center center' : 'bottom start'"
    :origin="smAndDown ? 'center center' : 'top start'"
    :max-width="smAndDown ? undefined : 360"
    :disabled="disabled || readonly"
  >
    <template #activator="{ props: activatorProps }">
      <VTextField
        v-bind="{ ...activatorProps, ...$attrs }"
        :model-value="displayValue"
        :label="label"
        :hint="hint"
        :persistent-hint="persistentHint"
        :error-messages="errorMessages"
        :disabled="disabled"
        :readonly="true"
        :density="density"
        :hide-details="hideDetails"
        :clearable="clearable && !disabled && !readonly && !!modelValue"
        prepend-inner-icon="bx-calendar"
        class="app-date-field"
        @click:clear.stop="clear"
      />
    </template>

    <VCard class="app-date-field__card" :class="{ 'app-date-field__card--mobile': smAndDown }">
      <VDatePicker
        :model-value="selectedDate"
        :min="minDate"
        :max="maxDate"
        :width="smAndDown ? undefined : 360"
        show-adjacent-months
        hide-header
        @update:model-value="select"
      />

      <VDivider />

      <VCardActions class="app-date-field__actions">
        <VBtn variant="text" size="small" @click="selectToday">
          {{ t('common.today') }}
        </VBtn>

        <VSpacer />

        <VBtn variant="text" size="small" color="secondary" @click="clear">
          {{ t('common.clear') }}
        </VBtn>
        <VBtn variant="text" size="small" @click="menuOpen = false">
          {{ t('common.close') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VMenu>
</template>

<style scoped>
/* The field is opened by the calendar popup, so the text itself isn't typed. */
.app-date-field :deep(input) {
  cursor: pointer;
}

.app-date-field__card {
  overflow: hidden;
}

/*
 * On phones the picker is centred over the page, so it takes most of the
 * width instead of the fixed 360px used in the desktop dropdown.
 */
.app-date-field__card--mobile {
  inline-size: min(92vw, 360px);
}

.app-date-field__card--mobile :deep(.v-date-picker),
.app-date-field__card--mobile :deep(.v-picker) {
  inline-size: 100%;
}

.app-date-field__actions {
  padding-block: 4px;
  padding-inline: 8px;
}
</style>
