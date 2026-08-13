<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  form: { type: Object, required: true },
  roleOptions: { type: Array, required: true },
  accountRoleOptions: { type: Array, required: true },

  /** Editing leaves the password blank unless it is being changed. */
  isEdit: { type: Boolean, default: false },
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
      <VIcon icon="bx-user-circle" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('admins.sections.account') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('admins.sections.accountDesc') }}
      </p>
    </div>
  </div>

  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.name"
        autocomplete="name"
        maxlength="255"
        :counter="255"
        :label="t('admins.fields.name')"
        :error-messages="form.errors.name"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.email"
        autocomplete="email"
        inputmode="email"
        maxlength="255"
        :counter="255"
        type="email"
        :label="t('admins.fields.email')"
        :error-messages="form.errors.email"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.username"
        autocomplete="username"
        maxlength="100"
        :counter="100"
        :label="t('admins.fields.username')"
        :hint="t('admins.hints.username')"
        persistent-hint
        :error-messages="form.errors.username"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.phone"
        autocomplete="tel"
        inputmode="tel"
        maxlength="20"
        :counter="20"
        :label="t('admins.fields.phone')"
        :error-messages="form.errors.phone"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.password"
        maxlength="255"
        type="password"
        autocomplete="new-password"
        :label="t('admins.fields.password')"
        :hint="isEdit ? t('admins.hints.passwordEdit') : undefined"
        :persistent-hint="isEdit"
        :error-messages="form.errors.password"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        v-model="form.password_confirmation"
        maxlength="255"
        type="password"
        autocomplete="new-password"
        :label="t('admins.fields.password_confirmation')"
        :error-messages="form.errors.password_confirmation"
      />
    </VCol>
  </VRow>

  <VDivider class="my-6" />

  <div class="d-flex align-center gap-3 mb-6">
    <VAvatar
      color="warning"
      variant="tonal"
      size="40"
    >
      <VIcon icon="bx-shield-quarter" />
    </VAvatar>
    <div>
      <h6 class="text-h6">
        {{ t('admins.sections.access') }}
      </h6>
      <p class="text-body-2 text-medium-emphasis mb-0">
        {{ t('admins.sections.accessDesc') }}
      </p>
    </div>
  </div>

  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <!--
        The options list is already narrowed server-side, and the Action
        refuses an unauthorised account type regardless of what is posted.
      -->
      <VSelect
        v-model="form.account_role"
        :items="accountRoleOptions"
        :label="t('admins.fields.account_role')"
        :error-messages="form.errors.account_role"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VSelect
        v-model="form.roles"
        :items="roleOptions"
        :label="t('admins.fields.roles')"
        :hint="t('admins.hints.roles')"
        persistent-hint
        multiple
        chips
        :error-messages="form.errors.roles"
      />
    </VCol>

    <VCol cols="12">
      <VSwitch
        v-model="form.is_active"
        :label="t('admins.fields.is_active')"
      />
    </VCol>
  </VRow>
</template>
