<script setup>
import { useI18n } from 'vue-i18n'
import OrganizationForm from './Partials/_OrganizationForm.vue'

defineProps({
  schoolSetting: { type: Object, default: null },
})

const emit = defineEmits(['next'])

const { t } = useI18n()
</script>

<template>
  <h6 class="text-h6 mb-1">
    {{ t('onboarding.steps.organizationTitle') }}
  </h6>
  <p class="text-body-2 text-medium-emphasis mb-6">
    {{ t('onboarding.steps.organizationDescription') }}
  </p>

  <OrganizationForm
    :school-setting="schoolSetting"
    submit-route="/onboarding/organization"
    :submit-label="t('onboarding.actions.next')"
    :on-saved="() => emit('next')"
  />
</template>
