<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import RoleForm from './_Form.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  role: { type: Object, required: true },
  permissions: { type: Array, required: true },
})

const { t } = useI18n()

const form = useForm({
  name: props.role.name,
  permissions: [...(props.role.permissions ?? [])],
})

/*
 * PUT rather than the POST+_method spoof: there is no file upload here, so
 * the multipart workaround other forms need does not apply.
 */
function submit() {
  form.put(`/access-control/roles/${props.role.id}`)
}
</script>

<template>
  <Head :title="t('roles.edit')" />

  <AppPageHeader
    :title="t('roles.edit')"
    :subtitle="role.label"
  />

  <VCard class="app-form-card">
    <VCardText>
      <form @submit.prevent="submit">
        <RoleForm
          :form="form"
          :permissions="permissions"
          :is-system="role.is_system"
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
        {{ t('common.update') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>
