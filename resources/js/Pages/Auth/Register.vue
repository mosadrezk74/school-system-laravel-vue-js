<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import BlankLayout from '@/layouts/blank.vue'
import logo from '@images/logo.svg?raw'

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

  <div class="auth-wrapper d-flex justify-center pa-4">
    <div class="auth-panel">
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
          <h1 class="text-h4 mb-0">
            {{ t('auth.registerTitle') }}
          </h1>
        </div>

        <VForm @submit.prevent="submit">
          <div class="d-flex flex-column gap-4">
            <VTextField
              v-model="form.name"
              maxlength="255"
              :counter="255"
              autofocus
              :label="t('auth.name')"
              autocomplete="name"
              prepend-inner-icon="bx-user"
              :error-messages="form.errors.name"
            />

            <VTextField
              v-model="form.email"
              inputmode="email"
              maxlength="255"
              :counter="255"
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
              autocomplete="new-password"
              prepend-inner-icon="bx-lock-alt"
              :error-messages="form.errors.password"
              :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
              @click:append-inner="isPasswordVisible = !isPasswordVisible"
            />

            <VTextField
              v-model="form.password_confirmation"
              maxlength="255"
              :label="t('auth.confirmPassword')"
              :type="isPasswordVisible ? 'text' : 'password'"
              autocomplete="new-password"
              prepend-inner-icon="bx-lock-alt"
              :error-messages="form.errors.password_confirmation"
            />

            <VBtn
              block
              size="large"
              type="submit"
              class="mt-2"
              :loading="form.processing"
              :disabled="form.processing"
            >
              {{ t('auth.register') }}
            </VBtn>
          </div>
        </VForm>

        <div class="text-center text-body-2 mt-6">
          <span>{{ t('auth.haveAccount') }}</span>
          <Link
            class="text-primary ms-1 font-weight-medium"
            href="/login"
          >
            {{ t('auth.login') }}
          </Link>
        </div>
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
