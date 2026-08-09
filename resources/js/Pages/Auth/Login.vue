<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import BlankLayout from '@/layouts/blank.vue'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?url'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?url'

defineOptions({ layout: BlankLayout })

defineProps({
  canResetPassword: { type: Boolean, default: false },
  status: { type: String, default: null },
})

const { t } = useI18n()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const isPasswordVisible = ref(false)

const submit = () => form.post('/login', {
  onFinish: () => form.reset('password'),
})
</script>

<template>
  <Head :title="t('auth.login')" />

  <div class="auth-wrapper d-flex justify-center pa-4">
    <div
      class="position-relative"
      style="width: 100%; max-width: 460px;"
    >
      <VImg
        :src="authV1TopShape"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />
      <VImg
        :src="authV1BottomShape"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <VCard
        class="auth-card"
        elevation="10"
        rounded="lg"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-4'"
      >
        <VCardItem class="justify-center pb-2">
          <Link
            href="/"
            class="app-logo"
          >
            <!-- eslint-disable vue/no-v-html -->
            <div
              class="d-flex justify-center"
              style="max-height: 56px;"
              v-html="logo"
            />
            <!-- eslint-enable -->
          </Link>
        </VCardItem>

        <VCardText class="text-center pt-2 pb-6">
          <h4 class="text-h4 font-weight-bold mb-2">
            {{ t('auth.welcomeBack') }}
          </h4>
          <p class="text-body-1 text-medium-emphasis mb-0">
            {{ t('auth.loginSubtitle') }}
          </p>
        </VCardText>

        <VCardText class="pt-0">
          <VAlert
            v-if="status"
            type="success"
            variant="tonal"
            class="mb-4"
          >
            {{ status }}
          </VAlert>

          <VForm @submit.prevent="submit">
            <VRow>
              <VCol
                cols="12"
                class="pb-1"
              >
                <VTextField
                  v-model="form.email"
                  autofocus
                  :label="t('auth.email')"
                  type="email"
                  autocomplete="username"
                  prepend-inner-icon="bx-envelope"
                  variant="outlined"
                  :error-messages="form.errors.email"
                />
              </VCol>

              <VCol
                cols="12"
                class="pt-1"
              >
                <VTextField
                  v-model="form.password"
                  :label="t('auth.password')"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="current-password"
                  prepend-inner-icon="bx-lock-alt"
                  variant="outlined"
                  :error-messages="form.errors.password"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center justify-space-between flex-wrap my-4">
                  <VCheckbox
                    v-model="form.remember"
                    :label="t('auth.rememberMe')"
                    density="compact"
                    hide-details
                  />
                </div>

                <VBtn
                  block
                  size="large"
                  type="submit"
                  :loading="form.processing"
                  :disabled="form.processing"
                >
                  {{ t('auth.login') }}
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>