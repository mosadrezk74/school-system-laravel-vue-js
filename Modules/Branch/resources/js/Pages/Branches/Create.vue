<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import BranchForm from './_Form.vue'

defineProps({
  managers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const form = useForm({
  name: '',
  code: '',
  address: '',
  city: '',
  phone: '',
  manager_id: null,
  is_main: false,
  is_active: true,
})

function submit() {
  form.post('/settings/branches')
}
</script>

<template>
  <Head :title="t('branch.addNew')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('branch.addNew') }}</VCardTitle>
      <VCardSubtitle>{{ t('branch.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <form @submit.prevent="submit">
        <BranchForm
          :form="form"
          :managers="managers"
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
        {{ t('common.save') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>