<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  admin: { type: Object, required: true },
  roleOptions: { type: Array, required: true },
  accountRoleOptions: { type: Array, required: true },
})

const { t } = useI18n()

const form = useForm({
  name: props.admin.name ?? '',
  email: props.admin.email ?? '',
  username: props.admin.username ?? '',

  // Left blank so an untouched form never rewrites the stored password.
  password: '',
  password_confirmation: '',
  phone: props.admin.phone ?? '',
  account_role: props.admin.account_role,
  roles: [...(props.admin.roles ?? [])],
  is_active: props.admin.is_active,
})

function submit() {
  form.put(`/access-control/admins/${props.admin.id}`)
}
</script>

<template>
  <Head :title="t('admins.edit')" />

  <AppPageHeader
    :title="t('admins.edit')"
    :subtitle="admin.name"
  />

  <VCard class="app-form-card">
    <VCardText>
      <form @submit.prevent="submit">
        <AdminForm
          :form="form"
          :role-options="roleOptions"
          :account-role-options="accountRoleOptions"
          is-edit
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/access-control/admins"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        :loading="form.processing"
        :disabled="form.processing"
        color="primary"
        @click="submit"
      >
        {{ t('common.update') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
