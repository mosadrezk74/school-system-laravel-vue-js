<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import BlankLayout from '@/layouts/blank.vue'
import logo from '@images/logo.svg?raw'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?url'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?url'

defineOptions({ layout: BlankLayout })

const { t } = useI18n()

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const isPasswordVisible = ref(false)

const submit = () => form.post('/register', {
  onFinish: () => form.reset('password', 'password_confirmation'),
})
</script>

<template>
  <Head :title="t('auth.register')" />

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
            {{ t('auth.registerTitle') }}
          </h4>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="submit">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.name"
                  autofocus
                  :label="t('auth.name')"
                  autocomplete="name"
                  :error-messages="form.errors.name"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.email"
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
                  autocomplete="new-password"
                  :error-messages="form.errors.password"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.password_confirmation"
                  :label="t('auth.confirmPassword')"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :error-messages="form.errors.password_confirmation"
                />

                <VBtn
                  block
                  type="submit"
                  class="mt-6"
                  :loading="form.processing"
                  :disabled="form.processing"
                >
                  {{ t('auth.register') }}
                </VBtn>
              </VCol>

              <VCol
                cols="12"
                class="text-body-1 text-center"
              >
                <span class="d-inline-block">{{ t('auth.haveAccount') }}</span>
                <Link
                  class="text-primary ms-1 d-inline-block text-body-1"
                  href="/login"
                >
                  {{ t('auth.login') }}
                </Link>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>
