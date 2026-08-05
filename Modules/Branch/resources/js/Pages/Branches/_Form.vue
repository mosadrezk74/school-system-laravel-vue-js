<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  form: { type: Object, required: true },
  managers: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

const { t } = useI18n()

// A trailing "*" on the label is the one cue every field needs, required or
// not — leaving it off already reads as optional, so there's no separate
// "Optional" marker to maintain in sync.
const required = label => `${label} *`

const activeTab = ref('basic')

// Field names per tab for error badge
const tabFields = {
  basic: ['name', 'code', 'is_main', 'is_active'],
  location: ['address', 'city', 'phone'],
  management: ['manager_id'],
}

const tabHasError = tab => tabFields[tab].some(field => !!props.form.errors[field])

// Manager options
const managerOptions = computed(() =>
  props.managers.map(m => ({
    title: `${m.name} (${m.email})`,
    value: m.id,
  })))
</script>

<template>
  <VTabs
    v-model="activeTab"
    show-arrows
    class="v-tabs-pill"
  >
    <VTab value="basic">
      <VIcon size="20" start icon="bx-building" />
      {{ $t('branch.sections.basic') }}
      <VIcon v-if="tabHasError('basic')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="location">
      <VIcon size="20" start icon="bx-map-pin" />
      {{ $t('branch.sections.location') }}
      <VIcon v-if="tabHasError('location')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="management">
      <VIcon size="20" start icon="bx-user" />
      {{ $t('branch.sections.management') }}
      <VIcon v-if="tabHasError('management')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <!-- Basic Information Tab -->
    <VWindowItem value="basic">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-building" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('branch.sections.basic') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('branch.sections.basicDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.name"
            :label="required(t('branch.fields.name'))"
            :hint="$t('branch.hints.name')"
            persistent-hint
            :error-messages="form.errors.name"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.code"
            :label="$t('branch.fields.code')"
            :hint="$t('branch.hints.code')"
            persistent-hint
            :error-messages="form.errors.code"
          />
        </VCol>

        <VCol cols="12" md="6" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_main"
            :label="$t('branch.fields.is_main')"
            color="primary"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('branch.hints.is_main') }}
          </p>
        </VCol>

        <VCol cols="12" md="6" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_active"
            :label="$t('branch.fields.is_active')"
            color="success"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('branch.hints.is_active') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>

    <!-- Location Tab -->
    <VWindowItem value="location">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-map-pin" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('branch.sections.location') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('branch.sections.locationDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.address"
            :label="$t('branch.fields.address')"
            :hint="$t('branch.hints.address')"
            persistent-hint
            :error-messages="form.errors.address"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.city"
            :label="$t('branch.fields.city')"
            :hint="$t('branch.hints.city')"
            persistent-hint
            :error-messages="form.errors.city"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.phone"
            :label="$t('branch.fields.phone')"
            :hint="$t('branch.hints.phone')"
            persistent-hint
            :error-messages="form.errors.phone"
          />
        </VCol>

        <VCol cols="12" md="6">
          <!-- Empty spacer for alignment -->
        </VCol>
      </VRow>
    </VWindowItem>

    <!-- Management Tab -->
    <VWindowItem value="management">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-user" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('branch.sections.management') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('branch.sections.managementDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="8">
          <VSelect
            v-model="form.manager_id"
            clearable
            :items="managerOptions"
            :label="$t('branch.fields.manager_id')"
            :hint="$t('branch.hints.manager_id')"
            persistent-hint
            :error-messages="form.errors.manager_id"
          />
        </VCol>

        <VCol cols="12" md="4">
          <!-- Empty spacer for alignment -->
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
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
