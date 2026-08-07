<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

const props = defineProps({
  form: { type: Object, required: true },
})

const { t } = useI18n()

const activeTab = ref('details')

const tabFields = {
  details: ['name', 'description', 'code', 'is_active'],
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
      <VIcon size="20" start icon="bx-purchase-tag" />
      {{ t('finance.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="details">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-purchase-tag" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('finance.feeCategories.sectionTitle') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('finance.feeCategories.sectionDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <TranslatableTextInput
            v-model="form.name"
            field="name"
            :label="t('finance.fields.name')"
            :errors="form.errors"
            required
          />
          <p class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('finance.hints.categoryName') }}
          </p>
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.code"
            :label="t('finance.fields.code')"
            :hint="t('finance.hints.code')"
            persistent-hint
            :error-messages="form.errors.code"
          />
        </VCol>

        <VCol cols="12">
          <TranslatableTextarea
            v-model="form.description"
            field="description"
            :label="t('finance.fields.description')"
            :errors="form.errors"
          />
          <p class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('finance.hints.description') }}
          </p>
        </VCol>

        <VCol cols="12" md="6">
          <VSwitch
            v-model="form.is_active"
            :label="t('finance.fields.is_active')"
            color="primary"
            :error-messages="form.errors.is_active"
          />
          <p class="text-caption text-medium-emphasis mb-0 mt-1">
            {{ t('finance.hints.isActive') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>
