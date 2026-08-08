<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import ReviewStep from './_ReviewStep.vue'
import SchoolInfoStep from './_SchoolInfoStep.vue'
import WelcomeStep from './_WelcomeStep.vue'

const props = defineProps({
  schoolSetting: { type: Object, default: null },
  rows: { type: Object, required: true },
})

const { t } = useI18n()

const lastStep = 3

/**
 * Language toggles reload the page (`preserveState: false`), which would reset
 * a plain ref back to step 1. Keeping the step in the URL hash survives that.
 */
function stepFromHash() {
  const parsed = Number.parseInt(window.location.hash.replace('#step-', ''), 10)

  return Number.isNaN(parsed) ? 1 : Math.min(Math.max(parsed, 1), lastStep)
}

const step = ref(stepFromHash())

watch(step, value => {
  window.history.replaceState(null, '', `#step-${value}`)
})

const languageRows = props.rows.data ?? []

function next() {
  step.value = Math.min(step.value + 1, lastStep)
}
</script>

<template>
  <Head :title="t('onboarding.title')" />

  <VCard>
    <VCardText>
      <VStepper v-model="step" :items="[
        t('onboarding.steps.welcome'),
        t('onboarding.steps.schoolInfo'),
        t('onboarding.steps.review'),
      ]" editable hide-actions>
        <template #item.1>
          <WelcomeStep :languages="languageRows" @next="next" />
        </template>
        <template #item.2>
          <SchoolInfoStep :school-setting="schoolSetting" @next="next" />
        </template>
        <template #item.3>
          <ReviewStep :school-setting="schoolSetting" :languages="languageRows" />
        </template>
      </VStepper>
    </VCardText>
  </VCard>
</template>
