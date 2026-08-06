<script setup>
import { useI18n } from 'vue-i18n'
import SchoolInfoForm from './Partials/_SchoolInfoForm.vue'

defineProps({
  schoolSetting: { type: Object, default: null },
})

const emit = defineEmits(['next'])

const { t } = useI18n()
</script>

<template>
  <h6 class="text-h6 mb-1">
    {{ t('onboarding.steps.schoolInfoTitle') }}
  </h6>
  <p class="text-body-2 text-medium-emphasis mb-6">
    {{ t('onboarding.steps.schoolInfoDescription') }}
  </p>

  <SchoolInfoForm
    :school-setting="schoolSetting"
    submit-route="/onboarding/school-info"
    :submit-label="t('onboarding.actions.next')"
    :on-saved="() => emit('next')"
  />
</template>
