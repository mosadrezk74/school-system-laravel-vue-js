<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  report: { type: Object, required: true },
  // Null unless the viewer holds the stack-trace permission; the server
  // decides, so hiding it here is presentation only, never the safeguard.
  trace: { type: String, default: null },
})

const { t } = useI18n()

const formatDate = value => value ? new Date(value).toLocaleString() : '—'

const requestRows = computed(() => [
  { label: t('systemErrors.fields.route'), value: props.report.route || '—' },
  { label: t('systemErrors.fields.method'), value: props.report.method || '—' },
  { label: t('systemErrors.fields.url'), value: props.report.url || '—' },
  { label: t('systemErrors.fields.requestId'), value: props.report.request_id || '—' },
  { label: t('systemErrors.fields.ip'), value: props.report.ip_address || '—' },
  { label: t('systemErrors.fields.userAgent'), value: props.report.user_agent || '—' },
])

const errorRows = computed(() => [
  { label: t('systemErrors.fields.errorCode'), value: props.report.error_code || '—' },
  { label: t('systemErrors.fields.level'), value: props.report.level },
  { label: t('systemErrors.fields.file'), value: props.report.file || '—' },
  { label: t('systemErrors.fields.line'), value: props.report.line ?? '—' },
  { label: t('systemErrors.occurrences'), value: props.report.occurrences },
  { label: t('systemErrors.firstSeen'), value: formatDate(props.report.first_seen_at) },
  { label: t('systemErrors.lastSeen'), value: formatDate(props.report.last_seen_at) },
])

function setStatus(status) {
  router.patch(`/system/errors/${props.report.id}`, { status }, { preserveScroll: true })
}
</script>

<template>
  <Head :title="report.exception_short" />

  <AppPageHeader
    :title="report.exception_short"
    :subtitle="report.message"
  >
    <template #actions>
      <VBtn
        variant="tonal"
        color="secondary"
        href="/system/errors"
        :as="Link"
      >
        {{ t('common.back') }}
      </VBtn>

      <VBtn
        v-if="report.status !== 'reviewed'"
        variant="tonal"
        color="warning"
        @click="setStatus('reviewed')"
      >
        {{ t('systemErrors.markReviewed') }}
      </VBtn>

      <VBtn
        v-if="report.status !== 'resolved'"
        color="success"
        @click="setStatus('resolved')"
      >
        {{ t('systemErrors.markResolved') }}
      </VBtn>

      <VBtn
        v-else
        variant="tonal"
        color="error"
        @click="setStatus('new')"
      >
        {{ t('systemErrors.markNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VRow>
    <VCol cols="12" md="6">
      <VCard class="mb-6">
        <VCardItem>
          <VCardTitle>{{ t('systemErrors.details') }}</VCardTitle>
          <template #append>
            <VChip :color="report.status_color" size="small">
              {{ report.status_label }}
            </VChip>
          </template>
        </VCardItem>
        <VCardText>
          <div
            v-for="row in errorRows"
            :key="row.label"
            class="d-flex justify-space-between gap-4 py-2 border-b"
          >
            <span class="text-medium-emphasis text-caption">{{ row.label }}</span>
            <span class="text-body-2 text-end text-break">{{ row.value }}</span>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard class="mb-6">
        <VCardItem>
          <VCardTitle>{{ t('systemErrors.request') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <div
            v-for="row in requestRows"
            :key="row.label"
            class="d-flex justify-space-between gap-4 py-2 border-b"
          >
            <span class="text-medium-emphasis text-caption">{{ row.label }}</span>
            <span class="text-body-2 text-end text-break">{{ row.value }}</span>
          </div>

          <div class="d-flex justify-space-between gap-4 py-2">
            <span class="text-medium-emphasis text-caption">{{ t('systemErrors.fields.user') }}</span>
            <span class="text-body-2 text-end">{{ report.user?.name ?? t('systemErrors.guest') }}</span>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('systemErrors.exception') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <div class="text-body-2 mb-4 text-break">
            <strong>{{ report.exception }}</strong>
          </div>
          <p class="text-body-2 text-medium-emphasis text-break">
            {{ report.message }}
          </p>

          <VDivider class="my-4" />

          <h6 class="text-subtitle-1 mb-2">
            {{ t('systemErrors.trace') }}
          </h6>

          <pre v-if="trace" class="trace-block">{{ trace }}</pre>

          <VAlert
            v-else
            type="info"
            variant="tonal"
            density="compact"
          >
            {{ t('systemErrors.traceHidden') }}
          </VAlert>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style scoped>
.trace-block {
  overflow-x: auto;
  padding: 1rem;
  border-radius: 6px;
  background: rgba(var(--v-theme-on-surface), 0.04);
  font-family: monospace;
  font-size: 0.75rem;
  line-height: 1.6;
  white-space: pre;
}

.text-break {
  /*
    `break-word` rather than `anywhere`: this span is a flex item, whose
    automatic minimum size is its min-content width. `anywhere` folds every
    character into that measurement, so a long unbroken value (a URL, a file
    path) can squeeze the column down to one character per line once the row
    runs short of space. `break-word` still wraps the value instead of letting
    it overflow, without feeding back into the sizing.
  */
  overflow-wrap: break-word;
}
</style>
