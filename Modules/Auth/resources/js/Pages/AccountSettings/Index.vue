<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  account: { type: Object, required: true },
  languageOptions: { type: Array, default: () => [] },
  themes: { type: Array, default: () => [] },
  timezones: { type: Array, default: () => [] },
})

const { t } = useI18n()

const required = label => `${label} *`

const activeTab = ref('account')

const profileForm = useForm({
  name: props.account.name,
  email: props.account.email,
  username: props.account.username,
  phone: props.account.phone,
  gender: props.account.gender,
  birth_date: props.account.birth_date,
  national_id: props.account.national_id,
  language: props.account.language,
  theme: props.account.theme,
  timezone: props.account.timezone,
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const deleteForm = useForm({
  password: '',
})

const confirmDelete = ref(false)

function submitProfile() {
  profileForm.patch('/account-settings/profile', { preserveScroll: true })
}

function submitPassword() {
  passwordForm.put('/account-settings/password', {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  })
}

function submitDelete() {
  deleteForm.delete('/account-settings', {
    preserveScroll: true,
  })
}

const profileFields = ['name', 'email', 'username', 'phone', 'gender', 'birth_date', 'national_id', 'language', 'theme', 'timezone']
const passwordFields = ['current_password', 'password', 'password_confirmation']

const tabHasError = (form, fields) => fields.some(field => !!form.errors[field])
</script>

<template>
  <Head :title="t('accountSettings.title')" />

  <AppPageHeader
    :title="t('accountSettings.title')"
    :subtitle="t('accountSettings.subtitle')"
  />

  <VCard>

    <VDivider />

    <VTabs v-model="activeTab" show-arrows class="v-tabs-pill">
      <VTab value="account">
        <VIcon size="20" start icon="bx-user" />
        {{ t('accountSettings.tabs.account') }}
        <VIcon v-if="tabHasError(profileForm, profileFields)" size="16" end icon="bx-error-circle" color="error" />
      </VTab>
      <VTab value="security">
        <VIcon size="20" start icon="bx-lock-open" />
        {{ t('accountSettings.tabs.security') }}
        <VIcon v-if="tabHasError(passwordForm, passwordFields)" size="16" end icon="bx-error-circle" color="error" />
      </VTab>
    </VTabs>

    <VDivider />

    <VWindow v-model="activeTab" class="disable-tab-transition">
      <VWindowItem value="account">
        <form @submit.prevent="submitProfile">
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="profileForm.name"
                  :label="required(t('accountSettings.fields.name'))"
                  :error-messages="profileForm.errors.name"
                  required
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="profileForm.email"
                  type="email"
                  :label="required(t('accountSettings.fields.email'))"
                  :error-messages="profileForm.errors.email"
                  required
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="profileForm.username"
                  :label="required(t('accountSettings.fields.username'))"
                  :error-messages="profileForm.errors.username"
                  required
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="profileForm.phone"
                  :label="t('accountSettings.fields.phone')"
                  :error-messages="profileForm.errors.phone"
                />
              </VCol>

              <VCol cols="12" md="4">
                <VSelect
                  v-model="profileForm.gender"
                  clearable
                  :items="[
                    { title: t('accountSettings.male'), value: 'male' },
                    { title: t('accountSettings.female'), value: 'female' },
                  ]"
                  :label="t('accountSettings.fields.gender')"
                  :error-messages="profileForm.errors.gender"
                />
              </VCol>

              <VCol cols="12" md="4">
                <AppDateField
                  v-model="profileForm.birth_date"
                  :label="t('accountSettings.fields.birth_date')"
                  :error-messages="profileForm.errors.birth_date"
                />
              </VCol>

              <VCol cols="12" md="4">
                <VTextField
                  v-model="profileForm.national_id"
                  :label="t('accountSettings.fields.national_id')"
                  :error-messages="profileForm.errors.national_id"
                />
              </VCol>

              <VCol cols="12" md="4">
                <VSelect
                  v-model="profileForm.language"
                  :items="languageOptions"
                  :label="required(t('accountSettings.fields.language'))"
                  :error-messages="profileForm.errors.language"
                  required
                />
              </VCol>

              <VCol cols="12" md="4">
                <VSelect
                  v-model="profileForm.theme"
                  :items="themes"
                  :label="required(t('accountSettings.fields.theme'))"
                  :error-messages="profileForm.errors.theme"
                  required
                />
              </VCol>

              <VCol cols="12" md="4">
                <VSelect
                  v-model="profileForm.timezone"
                  :items="timezones"
                  :label="required(t('accountSettings.fields.timezone'))"
                  :error-messages="profileForm.errors.timezone"
                  required
                />
              </VCol>
            </VRow>
          </VCardText>

          <VDivider />

          <VCardText class="d-flex justify-end">
            <VBtn :loading="profileForm.processing" type="submit">
              {{ t('common.save') }}
            </VBtn>
          </VCardText>
        </form>
      </VWindowItem>

      <VWindowItem value="security">
        <form @submit.prevent="submitPassword">
          <VCardText>
            <VRow>
              <VCol cols="12" md="4">
                <VTextField
                  v-model="passwordForm.current_password"
                  type="password"
                  :label="required(t('accountSettings.fields.current_password'))"
                  :error-messages="passwordForm.errors.current_password"
                  required
                />
              </VCol>

              <VCol cols="12" md="4">
                <VTextField
                  v-model="passwordForm.password"
                  type="password"
                  :label="required(t('accountSettings.fields.password'))"
                  :error-messages="passwordForm.errors.password"
                  required
                />
              </VCol>

              <VCol cols="12" md="4">
                <VTextField
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  :label="required(t('accountSettings.fields.password_confirmation'))"
                  required
                />
              </VCol>
            </VRow>
          </VCardText>

          <VDivider />

          <VCardText class="d-flex justify-end">
            <VBtn :loading="passwordForm.processing" type="submit">
              {{ t('common.save') }}
            </VBtn>
          </VCardText>
        </form>
      </VWindowItem>
    </VWindow>
  </VCard>

  <VCard v-if="activeTab === 'account'" class="mt-6" color="error" variant="outlined">
    <VCardItem>
      <VCardTitle>{{ t('accountSettings.deleteAccount.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('accountSettings.deleteAccount.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VDivider />

    <VCardText>
      <VCheckbox
        v-model="confirmDelete"
        :label="t('accountSettings.deleteAccount.confirm')"
      />

      <VTextField
        v-model="deleteForm.password"
        type="password"
        :disabled="!confirmDelete"
        :label="required(t('accountSettings.fields.password'))"
        :error-messages="deleteForm.errors.password"
      />
    </VCardText>

    <VDivider />

    <VCardText class="d-flex justify-end">
      <VBtn
        color="error"
        :disabled="!confirmDelete"
        :loading="deleteForm.processing"
        @click="submitDelete"
      >
        {{ t('accountSettings.deleteAccount.submit') }}
      </VBtn>
    </VCardText>
  </VCard>
</template>
