<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BranchForm from './_Form.vue'

const props = defineProps({
  branch: { type: Object, required: true },
  managers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: props.branch.name,
  code: props.branch.code,
  address: props.branch.address,
  city: props.branch.city,
  phone: props.branch.phone,
  manager_id: props.branch.manager_id,
  is_main: props.branch.is_main,
  is_active: props.branch.is_active,
})

function submit() {
  form.put(`/settings/branches/${props.branch.id}`)
}
</script>

<template>
  <Head :title="t('branch.edit')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('branch.edit') }}</VCardTitle>
      <VCardSubtitle>{{ branch.name }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <BranchForm
          :form="form"
          :managers="managers"
          is-edit
        />
      </form>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end gap-3">
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/settings/branches'"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        :loading="form.processing"
        @click="submit"
        color="primary"
      >
        {{ t('common.update') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>