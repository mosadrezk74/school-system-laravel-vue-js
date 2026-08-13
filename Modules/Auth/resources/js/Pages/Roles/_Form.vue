<script setup>
import { useI18n } from 'vue-i18n'
import PermissionPicker from './_PermissionPicker.vue'

const props = defineProps({
  form: { type: Object, required: true },
  permissions: { type: Array, required: true },

  /** System roles keep their name; only their permissions may change. */
  isSystem: { type: Boolean, default: false },
})

const { t } = useI18n()
</script>

<template>
  <div class="d-flex align-center gap-3 mb-6">
    <VAvatar
      color="primary"
      variant="tonal"
      size="40"
    >
      <VIcon icon="bx-shield-quarter" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('roles.sections.details') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('roles.sections.detailsDesc') }}
      </p>
    </div>
  </div>

  <VAlert
    v-if="isSystem"
    type="info"
    variant="tonal"
    class="mb-4"
  >
    {{ t('roles.systemRoleNotice') }}
  </VAlert>

  <VRow class="mb-2">
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.name"
        autocomplete="name"
        maxlength="100"
        :counter="100"
        :label="t('roles.fields.name')"
        :hint="t('roles.hints.name')"
        persistent-hint
        :disabled="isSystem"
        :error-messages="form.errors.name"
      />
    </VCol>
  </VRow>

  <VDivider class="my-6" />

  <PermissionPicker
    v-model="props.form.permissions"
    :groups="permissions"
  />

  <p
    v-if="form.errors.permissions"
    class="text-error text-body-2 mt-2"
  >
    {{ form.errors.permissions }}
  </p>
</template>
