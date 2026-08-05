<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  form: { type: Object, required: true },
  managers: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

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
  }))
)

// Helper to format coordinates
const formatCoordinate = (value) => {
  if (value === null || value === undefined || value === '') return null
  return parseFloat(value)
}
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
      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.name"
            :label="$t('branch.fields.name')"
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
:deep(.v-input:not(.v-input--error) .v-messages) {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.75rem;
  opacity: var(--v-medium-emphasis-opacity);
}
</style>