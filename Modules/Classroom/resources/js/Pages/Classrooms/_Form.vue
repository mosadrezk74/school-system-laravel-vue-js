<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  form: { type: Object, required: true },
  roomTypes: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

const activeTab = ref('basic')

const tabFields = {
  basic: ['name', 'code', 'room_type', 'capacity'],
  location: ['building', 'floor'],
  facilities: ['has_projector', 'has_smartboard'],
  status: ['is_active'],
}

const tabHasError = tab => tabFields[tab].some(field => !!props.form.errors[field])

</script>

<template>
  <VTabs v-model="activeTab" show-arrows class="v-tabs-pill">
    <VTab value="basic">
      <VIcon size="20" start icon="bx-info-circle" />
      {{ $t('classroom.sections.basic') }}
      <VIcon v-if="tabHasError('basic')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="location">
      <VIcon size="20" start icon="bx-map-pin" />
      {{ $t('classroom.sections.location') }}
      <VIcon v-if="tabHasError('location')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="facilities">
      <VIcon size="20" start icon="bx-wrench" />
      {{ $t('classroom.sections.facilities') }}
      <VIcon v-if="tabHasError('facilities')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="status">
      <VIcon size="20" start icon="bx-toggle-left" />
      {{ $t('classroom.sections.status') }}
      <VIcon v-if="tabHasError('status')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="basic">
      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.name"
            :label="$t('classroom.fields.name')"
            :hint="$t('classroom.hints.name')"
            persistent-hint
            :error-messages="form.errors.name"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.code"
            :label="$t('classroom.fields.code')"
            :hint="$t('classroom.hints.code')"
            persistent-hint
            :error-messages="form.errors.code"
          />
        </VCol>


        <VCol cols="12" md="6">
          <VSelect
            v-model="form.room_type"
            :items="roomTypes"
            :label="$t('classroom.fields.room_type')"
            :hint="$t('classroom.hints.room_type')"
            persistent-hint
            :error-messages="form.errors.room_type"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.capacity"
            type="number"
            min="1"
            max="999"
            :label="$t('classroom.fields.capacity')"
            :hint="$t('classroom.hints.capacity')"
            persistent-hint
            :error-messages="form.errors.capacity"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="location">
      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.building"
            :label="$t('classroom.fields.building')"
            :hint="$t('classroom.hints.building')"
            persistent-hint
            :error-messages="form.errors.building"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.floor"
            type="number"
            min="-5"
            max="50"
            :label="$t('classroom.fields.floor')"
            :hint="$t('classroom.hints.floor')"
            persistent-hint
            :error-messages="form.errors.floor"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="facilities">
      <VRow>
        <VCol cols="12" md="6" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.has_projector"
            :label="$t('classroom.fields.has_projector')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('classroom.hints.has_projector') }}
          </p>
        </VCol>

        <VCol cols="12" md="6" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.has_smartboard"
            :label="$t('classroom.fields.has_smartboard')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('classroom.hints.has_smartboard') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="status">
      <VRow>
        <VCol cols="12" md="6" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_active"
            :label="$t('classroom.fields.is_active')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('classroom.hints.is_active') }}
          </p>
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
