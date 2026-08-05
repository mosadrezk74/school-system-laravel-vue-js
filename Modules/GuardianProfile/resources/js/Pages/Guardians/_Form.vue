<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  form: { type: Object, required: true },
  isEdit: { type: Boolean, default: false },
})

const { t } = useI18n()

// A trailing "*" on the label is the one cue every field needs, required or
// not — leaving it off already reads as optional, so there's no separate
// "Optional" marker to maintain in sync.
const required = label => `${label} *`
</script>

<template>
  <div class="d-flex align-center gap-3 mb-6">
    <VAvatar color="primary" variant="tonal" size="40">
      <VIcon icon="bx-user" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ $t('guardians.sections.account') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ $t('guardians.sections.accountDesc') }}
      </p>
    </div>
  </div>

  <VRow>
    <VCol cols="12" md="6">
      <VTextField
        v-model="form.name"
        :label="required(t('guardians.name'))"
        :hint="$t('guardians.hints.name')"
        persistent-hint
        :error-messages="form.errors.name"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.email"
        type="email"
        :label="required(t('guardians.email'))"
        :hint="$t('guardians.hints.email')"
        persistent-hint
        :error-messages="form.errors.email"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.username"
        :label="required(t('guardians.username'))"
        :hint="$t('guardians.hints.username')"
        persistent-hint
        :error-messages="form.errors.username"
        required
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model="form.password"
        type="password"
        :label="isEdit ? t('guardians.password') : required(t('guardians.password'))"
        :hint="isEdit ? $t('guardians.hints.passwordHint') : $t('guardians.hints.password')"
        persistent-hint
        :error-messages="form.errors.password"
        :required="!isEdit"
      />
    </VCol>

    <VCol cols="12" md="4">
      <VTextField
        v-model="form.phone"
        :label="$t('guardians.phone')"
        :hint="$t('guardians.hints.phone')"
        persistent-hint
        :error-messages="form.errors.phone"
      />
    </VCol>

    <VCol cols="12" md="4">
      <VTextField
        v-model="form.national_id"
        :label="$t('guardians.nationalId')"
        :hint="$t('guardians.hints.nationalId')"
        persistent-hint
        :error-messages="form.errors.national_id"
      />
    </VCol>

    <VCol cols="12" md="4">
      <VTextField
        v-model="form.occupation"
        :label="$t('guardians.occupation')"
        :hint="$t('guardians.hints.occupation')"
        persistent-hint
        :error-messages="form.errors.occupation"
      />
    </VCol>

    <VCol cols="12" class="d-flex flex-column justify-center">
      <VSwitch
        v-model="form.is_active"
        :label="$t('guardians.active')"
      />
      <p class="text-caption text-medium-emphasis mb-0">
        {{ $t('guardians.hints.active') }}
      </p>
    </VCol>
  </VRow>
</template>

<style scoped>
/*
 * The field-level hint doubles as help text here, not a brief input format
 * note, so it needs to read clearly as secondary/quiet — smaller and greyer
 * than the label above it, matching the .text-caption/.text-medium-emphasis
 * pair already used for the switch captions in this same form.
 *
 * The template's own _field.scss colours the hint with the active theme
 * colour while a field is focused (v-messages inherits that colour), so
 * `color` has to be forced here too — opacity alone isn't enough to override
 * it back to a neutral grey. Scoped to :not(.v-input--error) so a real
 * validation message underneath still shows in the theme's error colour.
 */
:deep(.v-input:not(.v-input--error) .v-messages) {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.75rem;
  opacity: var(--v-medium-emphasis-opacity);
}
</style>
