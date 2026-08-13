<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'

const props = defineProps({
  form: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  providers: { type: Array, default: () => [] },
})

const { t } = useI18n()

const categoryOptions = computed(() => props.categories.map(item => ({
  title: item.label,
  value: item.value,
})))

const providerOptions = computed(() => props.providers.map(provider => ({
  title: provider.name,
  value: provider.code,
})))

const selectedProvider = computed(() => props.providers.find(p => p.code === props.form.code) ?? null)

watch(() => props.form.code, code => {
  const provider = props.providers.find(p => p.code === code)

  if (!provider)
    return

  props.form.category = provider.category
  props.form.columns = provider.columns
  props.form.filters = provider.filters
})

const activeTab = ref('details')

const tabFields = {
  details: ['name', 'code', 'category', 'is_active'],
}

const tabHasError = tab => tabFields[tab].some(field =>
  Object.keys(props.form.errors).some(key => key === field || key.startsWith(`${field}.`)))
</script>

<template>
  <VTabs
    v-model="activeTab"
    show-arrows
    class="v-tabs-pill"
  >
    <VTab value="details">
      <VIcon size="20" start icon="bx-file" />
      {{ t('report.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="details">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-file" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('report.sections.details') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('report.sections.detailsDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <TranslatableTextInput
            v-model="form.name"
            :maxlength="100"
            field="name"
            :label="t('report.fields.name')"
            :errors="form.errors"
            required
          />
          <p class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('report.hints.name') }}
          </p>
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.code"
            :items="providerOptions"
            :label="t('report.fields.code')"
            :hint="t('report.hints.code')"
            persistent-hint
            clearable
            :error-messages="form.errors.code"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.category"
            :items="categoryOptions"
            :label="t('report.fields.category')"
            :hint="t('report.hints.category')"
            persistent-hint
            clearable
            :error-messages="form.errors.category"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSwitch
            v-model="form.is_active"
            :label="t('report.fields.is_active')"
            color="primary"
            :error-messages="form.errors.is_active"
          />
          <p class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('report.hints.isActive') }}
          </p>
        </VCol>

        <VCol v-if="selectedProvider" cols="12">
          <VAlert type="info" variant="tonal" density="compact">
            {{ t('report.inheritedMetadata', {
              filters: selectedProvider.filters.length,
              columns: selectedProvider.columns.length,
            }) }}
          </VAlert>
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>
