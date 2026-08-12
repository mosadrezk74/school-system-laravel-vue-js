<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  roleOptions: { type: Array, required: true },
  accountRoleOptions: { type: Array, required: true },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  email: '',
  username: '',
  password: '',
  password_confirmation: '',
  phone: '',
  account_role: 'admin',
  roles: ['admin'],
  is_active: true,
})

function submit() {
  form.post('/access-control/admins')
}
</script>

<template>
  <Head :title="t('admins.addNew')" />

  <AppPageHeader
    :title="t('admins.addNew')"
    :subtitle="t('admins.subtitle')"
  />

  <VCard class="app-form-card">
    <VCardText>
      <form @submit.prevent="submit">
        <AdminForm
          :form="form"
          :role-options="roleOptions"
          :account-role-options="accountRoleOptions"
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
        {{ t('common.save') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
