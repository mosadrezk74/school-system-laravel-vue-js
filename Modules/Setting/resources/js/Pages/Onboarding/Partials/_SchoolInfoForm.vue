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
  school_name: props.schoolSetting?.school_name || '',
  school_code: props.schoolSetting?.school_code || '',
  timezone: props.schoolSetting?.timezone || '',
  currency: props.schoolSetting?.currency || '',
  currency_symbol: props.schoolSetting?.currency_symbol || '',
  logo: null,
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
        <VTextField
          v-model="form.school_name"
          maxlength="255"
          :counter="255"
          :label="`${t('onboarding.fields.schoolName')} *`"
          :error-messages="form.errors.school_name"
          required
        />
      </VCol>

      <VCol cols="12" md="6">
        <VTextField
          v-model="form.school_code"
          maxlength="50"
          :counter="50"
          :label="t('onboarding.fields.schoolCode')"
          :error-messages="form.errors.school_code"
        />
      </VCol>

      <VCol cols="12" md="4">
        <VTextField
          v-model="form.timezone"
          :label="t('onboarding.fields.timezone')"
          :error-messages="form.errors.timezone"
          placeholder="Asia/Riyadh"
        />
      </VCol>

      <VCol cols="12" md="4">
        <VTextField
          v-model="form.currency"
          maxlength="3"
          :counter="3"
          :label="t('onboarding.fields.currency')"
          :error-messages="form.errors.currency"
          placeholder="SAR"
        />
      </VCol>

      <VCol cols="12" md="4">
        <VTextField
          v-model="form.currency_symbol"
          maxlength="10"
          :counter="10"
          :label="t('onboarding.fields.currencySymbol')"
          :error-messages="form.errors.currency_symbol"
        />
      </VCol>

      <VCol cols="12">
        <VFileInput
          v-model="form.logo"
          :label="t('onboarding.fields.logo')"
          accept="image/*"
          prepend-icon="bx-image"
          :error-messages="form.errors.logo"
        />
      </VCol>
    </VRow>

    <div class="d-flex justify-end mt-4">
      <VBtn :loading="form.processing" @click="submit">
        {{ submitLabel || t('common.save') }}
      </VBtn>
    </div>
  </form>
</template>
