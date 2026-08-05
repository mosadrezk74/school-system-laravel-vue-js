<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import BlankLayout from '@/layouts/blank.vue'
import logo from '@images/logo.svg?raw'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?url'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?url'

defineOptions({ layout: BlankLayout })

defineProps({
  canResetPassword: { type: Boolean, default: false },
  status: { type: String, default: null },
})

const { t } = useI18n()

// useForm carries validation errors and the in-flight state for us, so the
// page never has to parse a response or track loading by hand.
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

  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
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
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'"
      >
        <VCardItem class="justify-center">
          <Link
            href="/"
            class="app-logo"
          >
            <!-- eslint-disable vue/no-v-html -->
            <div
              class="d-flex"
              v-html="logo"
            />
            <!-- eslint-enable -->
            <h1 class="app-logo-title">
              {{ $page.props.name }}
            </h1>
          </Link>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            {{ t('auth.welcome', { app: $page.props.name }) }}
          </h4>
          <p class="mb-0">
            {{ t('auth.loginSubtitle') }}
          </p>
        </VCardText>

        <VCardText>
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
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  autofocus
                  :label="t('auth.email')"
                  type="email"
                  autocomplete="username"
                  :error-messages="form.errors.email"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  :label="t('auth.password')"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="current-password"
                  :error-messages="form.errors.password"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center justify-space-between flex-wrap my-6">
                  <VCheckbox
                    v-model="form.remember"
                    :label="t('auth.rememberMe')"
                  />

                  <Link
                    v-if="canResetPassword"
                    class="text-primary"
                    href="/forgot-password"
                  >
                    {{ t('auth.forgotPassword') }}
                  </Link>
                </div>

                <VBtn
                  block
                  type="submit"
                  :loading="form.processing"
                  :disabled="form.processing"
                >
                  {{ t('auth.login') }}
                </VBtn>
              </VCol>

              <VCol
                cols="12"
                class="text-body-1 text-center"
              >
                <span class="d-inline-block">{{ t('auth.noAccount') }}</span>
                <Link
                  class="text-primary ms-1 d-inline-block text-body-1"
                  href="/register"
                >
                  {{ t('auth.createAccount') }}
                </Link>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>
