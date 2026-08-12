<script setup>
/**
 * The terms screen for families and staff alike.
 *
 * Like the notifications page, this declares no layout of its own: it is
 * reachable from every portal, so it inherits whichever shell the signed-in
 * user already has.
 */
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppEmptyState from '@/components/AppEmptyState.vue'

defineProps({
  term: { type: Object, default: null },
  acceptedCurrent: { type: Boolean, default: false },
  acceptedAt: { type: String, default: null },
})

const { t } = useI18n()

/*
 * The checkbox only gates the button in this browser. Whether an acceptance
 * is valid — and which version it applies to — is decided entirely by the
 * server, which reads the published version itself and ignores anything the
 * client sends.
 */
const agreed = ref(false)
const form = useForm({})

function accept() {
  form.post('/terms/accept', { preserveScroll: true })
}

const formatDateTime = value => (value ? new Date(value).toLocaleString() : '—')
</script>

<template>
  <Head :title="t('schoolTerms.portal.title')" />

  <VRow justify="center">
    <VCol
      cols="12"
      md="10"
      lg="8"
    >
      <AppEmptyState
        v-if="!term"
        :title="t('schoolTerms.portal.none')"
        icon="bx-file"
      />

      <template v-else>
        <VCard>
          <VCardItem>
            <VCardTitle>{{ term.title }}</VCardTitle>
            <VCardSubtitle>
              {{ t('schoolTerms.versionLabel', { version: term.version }) }}
            </VCardSubtitle>
          </VCardItem>

          <VDivider />

          <VCardText>
            <div class="school-terms-content text-body-1">
              {{ term.content }}
            </div>
          </VCardText>
        </VCard>

        <VAlert
          v-if="acceptedCurrent"
          type="success"
          variant="tonal"
          class="mt-4"
        >
          {{ t('schoolTerms.portal.alreadyAccepted', { date: formatDateTime(acceptedAt) }) }}
        </VAlert>

        <VCard
          v-else
          class="mt-4"
        >
          <VCardText>
            <VCheckbox
              v-model="agreed"
              :label="t('schoolTerms.portal.agreeLabel')"
              hide-details
            />
          </VCardText>

          <VCardActions class="px-4 pb-4">
            <VSpacer />
            <VBtn
              color="primary"
              :disabled="!agreed || form.processing"
              :loading="form.processing"
              @click="accept"
            >
              {{ t('schoolTerms.portal.accept') }}
            </VBtn>
          </VCardActions>
        </VCard>
      </template>
    </VCol>
  </VRow>
</template>

<style scoped>
.school-terms-content {
  white-space: pre-wrap;
  overflow-wrap: anywhere;
}
</style>
