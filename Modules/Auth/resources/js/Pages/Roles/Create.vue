<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import RoleForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

defineProps({
  permissions: { type: Array, required: true },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  permissions: [],
})

function submit() {
  form.post('/access-control/roles')
}
</script>

<template>
  <Head :title="t('roles.addNew')" />

  <AppPageHeader
    :title="t('roles.addNew')"
    :subtitle="t('roles.subtitle')"
  />

  <VCard class="app-form-card">
    <VCardText>
      <form @submit.prevent="submit">
        <RoleForm
          :form="form"
          :permissions="permissions"
        />
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        href="/access-control/roles"
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
