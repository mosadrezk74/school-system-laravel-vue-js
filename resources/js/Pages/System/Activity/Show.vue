<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  activity: { type: Object, required: true },

  /**
   * The academic year and semester the log was read under, resolved
   * server-side from the academic context rather than from the query string.
   */
  context: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const formatDate = value => value ? new Date(value).toLocaleString() : '—'

const entryRows = computed(() => [
  { label: t('systemActivity.fields.log'), value: props.activity.log_name || '—' },
  { label: t('systemActivity.fields.event'), value: props.activity.event || '—' },
  { label: t('systemActivity.fields.subject'), value: props.activity.subject_type_short
    ? `${props.activity.subject_type_short} #${props.activity.subject_id}`
    : '—' },
  { label: t('systemActivity.fields.causer'), value: props.activity.causer?.name ?? t('systemActivity.system') },
  { label: t('systemActivity.fields.occurredAt'), value: formatDate(props.activity.created_at) },
])

/*
 * The academic context describes the request, not the stored row — the log
 * has no academic columns — so it is shown in its own panel rather than
 * mixed in with the entry's own fields.
 */
const contextRows = computed(() => [
  { label: t('systemActivity.fields.academicYear'), value: props.context?.academic_year_id ?? '—' },
  { label: t('systemActivity.fields.semester'), value: props.context?.semester_id ?? '—' },
])

const hasChanges = computed(() => {
  const changes = props.activity.attribute_changes

  return Boolean(changes && Object.keys(changes).length > 0)
})

const hasProperties = computed(() => {
  const properties = props.activity.properties

  return Boolean(properties && Object.keys(properties).length > 0)
})

const asJson = value => JSON.stringify(value, null, 2)
</script>

<template>
  <Head :title="activity.description" />

  <AppPageHeader
    :title="activity.description"
    :subtitle="activity.subject_type_short
      ? `${activity.subject_type_short} #${activity.subject_id}`
      : t('systemActivity.title')"
  >
    <template #actions>
      <VBtn
        variant="tonal"
        color="secondary"
        href="/system/activity"
        :as="Link"
      >
        {{ t('common.back') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VRow>
    <VCol cols="12" md="6">
      <VCard class="mb-6">
        <VCardItem>
          <VCardTitle>{{ t('systemActivity.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <div
            v-for="row in entryRows"
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
          <VCardTitle>{{ t('systemActivity.academicContext') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <p class="text-body-2 text-medium-emphasis mb-3">
            {{ t('systemActivity.academicContextHint') }}
          </p>

          <div
            v-for="row in contextRows"
            :key="row.label"
            class="d-flex justify-space-between gap-4 py-2 border-b"
          >
            <span class="text-medium-emphasis text-caption">{{ row.label }}</span>
            <span class="text-body-2 text-end">{{ row.value }}</span>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('systemActivity.payload') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <h6 class="text-subtitle-1 mb-2">
            {{ t('systemActivity.fields.changes') }}
          </h6>

          <pre v-if="hasChanges" class="payload-block">{{ asJson(activity.attribute_changes) }}</pre>

          <VAlert
            v-else
            type="info"
            variant="tonal"
            density="compact"
          >
            {{ t('systemActivity.noChanges') }}
          </VAlert>

          <VDivider class="my-4" />

          <h6 class="text-subtitle-1 mb-2">
            {{ t('systemActivity.fields.properties') }}
          </h6>

          <pre v-if="hasProperties" class="payload-block">{{ asJson(activity.properties) }}</pre>

          <VAlert
            v-else
            type="info"
            variant="tonal"
            density="compact"
          >
            {{ t('systemActivity.noProperties') }}
          </VAlert>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style scoped>
.payload-block {
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
    character into that measurement, so a long unbroken value can squeeze the
    column down to one character per line once the row runs short of space.
  */
  overflow-wrap: break-word;
}
</style>
