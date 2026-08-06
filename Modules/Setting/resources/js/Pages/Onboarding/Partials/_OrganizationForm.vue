<script setup>
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  schoolSetting: { type: Object, default: null },
  submitRoute: { type: String, required: true },
  submitLabel: { type: String, default: null },
  onSaved: { type: Function, default: null },
})

const { t } = useI18n()

const form = useForm({
  multi_branch: props.schoolSetting?.settings?.multi_branch ?? false,
  multi_academic_year: props.schoolSetting?.settings?.multi_academic_year ?? false,
})

function submit() {
  form.post(props.submitRoute, {
    preserveScroll: true,
    onSuccess: () => props.onSaved?.(),
  })
}
</script>

<template>
  <form @submit.prevent="submit">
    <VRow>
      <VCol cols="12" md="6">
        <VSwitch
          v-model="form.multi_branch"
          :label="t('onboarding.fields.multiBranch')"
        />
        <p class="text-caption text-medium-emphasis mb-0">
          {{ t('onboarding.hints.multiBranch') }}
        </p>
      </VCol>

      <VCol cols="12" md="6">
        <VSwitch
          v-model="form.multi_academic_year"
          :label="t('onboarding.fields.multiAcademicYear')"
        />
        <p class="text-caption text-medium-emphasis mb-0">
          {{ t('onboarding.hints.multiAcademicYear') }}
        </p>
      </VCol>
    </VRow>

    <div class="d-flex justify-end mt-4">
      <VBtn :loading="form.processing" @click="submit">
        {{ submitLabel || t('common.save') }}
      </VBtn>
    </div>
  </form>
</template>
