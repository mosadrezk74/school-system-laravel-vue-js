<script setup>
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import ModulesStep from './_ModulesStep.vue'
import OrganizationStep from './_OrganizationStep.vue'
import ReviewStep from './_ReviewStep.vue'
import SchoolInfoStep from './_SchoolInfoStep.vue'
import LanguagesStep from './_LanguagesStep.vue'
import WelcomeStep from './_WelcomeStep.vue'

const props = defineProps({
  schoolSetting: { type: Object, default: null },
  rows: { type: Object, required: true },
  modules: { type: Array, required: true },
})

const { t } = useI18n()

const step = ref(1)

const languageRows = props.rows.data ?? []

function next() {
  step.value = Math.min(step.value + 1, 6)
}
</script>

<template>
  <Head :title="t('onboarding.title')" />

  <VCard>
    <VCardText>
      <VStepper v-model="step" :items="[
        t('onboarding.steps.welcome'),
        t('onboarding.steps.schoolInfo'),
        t('onboarding.steps.languages'),
        t('onboarding.steps.organization'),
        t('onboarding.steps.modules'),
        t('onboarding.steps.review'),
      ]" editable hide-actions>
        <template #item.1>
          <WelcomeStep @next="next" />
        </template>
        <template #item.2>
          <SchoolInfoStep :school-setting="schoolSetting" @next="next" />
        </template>
        <template #item.3>
          <LanguagesStep :languages="languageRows" @next="next" />
        </template>
        <template #item.4>
          <OrganizationStep :school-setting="schoolSetting" @next="next" />
        </template>
        <template #item.5>
          <ModulesStep :modules="modules" @next="next" />
        </template>
        <template #item.6>
          <ReviewStep :school-setting="schoolSetting" :languages="languageRows" :modules="modules" />
        </template>
      </VStepper>
    </VCardText>
  </VCard>
</template>
