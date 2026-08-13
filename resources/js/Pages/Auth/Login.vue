<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import BlankLayout from '@/layouts/blank.vue'
import logo from '@images/logo.svg?raw'

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
    <div class="auth-panel">
      <!--
        The template's decorative corner shapes are gone: they were purely
        ornamental, did not adapt to the palette switcher, and are exactly the
        "decorative elements that don't provide information" the brief rules
        out. The card now stands on its own against the page background.
      -->
      <div class="text-center mb-6">
        <Link
          href="/"
          class="d-inline-flex justify-center text-primary"
          :aria-label="$page.props.name"
        >
          <!-- eslint-disable vue/no-v-html -->
          <div
            class="auth-logo"
            v-html="logo"
          />
          <!-- eslint-enable -->
        </Link>
      </div>

      <VCard class="pa-6 pa-sm-8">
        <div class="text-center mb-6">
          <h1 class="text-h4 mb-2">
            {{ t('auth.welcomeBack') }}
          </h1>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('auth.loginSubtitle') }}
          </p>
        </div>

        <VAlert
          v-if="status"
          type="success"
          class="mb-6"
        >
          {{ status }}
        </VAlert>

        <VForm @submit.prevent="submit">
          <div class="d-flex flex-column gap-4">
            <VTextField
              v-model="form.email"
              inputmode="email"
              maxlength="255"
              autofocus
              :label="t('auth.email')"
              type="email"
              autocomplete="username"
              prepend-inner-icon="bx-envelope"
              :error-messages="form.errors.email"
            />

            <VTextField
              v-model="form.password"
              maxlength="255"
              :label="t('auth.password')"
              :type="isPasswordVisible ? 'text' : 'password'"
              autocomplete="current-password"
              prepend-inner-icon="bx-lock-alt"
              :error-messages="form.errors.password"
              :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
              @click:append-inner="isPasswordVisible = !isPasswordVisible"
            />

            <div class="d-flex align-center justify-space-between flex-wrap gap-2">
              <VCheckbox
                v-model="form.remember"
                :label="t('auth.rememberMe')"
                density="compact"
                hide-details
              />

              <!--
                `canResetPassword` was already being passed from the server but
                nothing rendered it, so users had no route to recovery even
                though Fortify's forgot-password page was live.
              -->
              <Link
                v-if="canResetPassword"
                href="/forgot-password"
                class="text-primary text-body-2 font-weight-medium"
              >
                {{ t('auth.forgotPassword') }}
              </Link>
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
          </div>
        </VForm>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.auth-panel {
  inline-size: 100%;
  max-inline-size: 26rem;
}

.auth-logo :deep(svg) {
  block-size: 2.5rem;
  inline-size: auto;
}
</style>
