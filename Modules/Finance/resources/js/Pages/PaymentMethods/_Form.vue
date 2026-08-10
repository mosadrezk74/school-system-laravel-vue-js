<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import TranslatableTextarea from '@/components/TranslatableTextarea.vue'

const props = defineProps({
  form: { type: Object, required: true },
  types: { type: Array, default: () => [] },
})

const { t } = useI18n()

const typeOptions = computed(() => props.types.map(item => ({ title: item.label, value: item.value })))

/*
 * The schema carries every field for every method, but showing a bank IBAN on
 * a cash entry only invites it to be filled in wrongly. The type drives which
 * groups appear; values already stored stay untouched either way.
 */
const showsPaymentUrl = computed(() => props.form.type === 'online')
const showsBankDetails = computed(() => props.form.type === 'bank_transfer')
</script>

<template>
  <VRow>
    <VCol cols="12" md="6">
      <TranslatableTextInput
        v-model="form.name"
        field="name"
        :label="t('finance.fields.name')"
        :errors="form.errors"
        required
      />
    </VCol>

    <VCol cols="12" md="3">
      <VTextField
        v-model="form.code"
        :label="t('finance.fields.code')"
        :error-messages="form.errors.code"
        required
      />
    </VCol>

    <VCol cols="12" md="3">
      <VSelect
        v-model="form.type"
        :label="t('finance.fields.type')"
        :items="typeOptions"
        :error-messages="form.errors.type"
        required
      />
    </VCol>

    <VCol cols="12">
      <TranslatableTextarea
        v-model="form.description"
        field="description"
        :label="t('finance.fields.description')"
        :errors="form.errors"
      />
    </VCol>

    <VCol v-if="showsPaymentUrl" cols="12">
      <VTextField
        v-model="form.payment_url"
        type="url"
        :label="t('finance.fields.payment_url')"
        placeholder="https://"
        :error-messages="form.errors.payment_url"
      />
    </VCol>

    <template v-if="showsBankDetails">
      <VCol cols="12" md="6">
        <VTextField
          v-model="form.bank_name"
          :label="t('finance.fields.bank_name')"
          :error-messages="form.errors.bank_name"
        />
      </VCol>

      <VCol cols="12" md="6">
        <VTextField
          v-model="form.account_name"
          :label="t('finance.fields.account_name')"
          :error-messages="form.errors.account_name"
        />
      </VCol>

      <VCol cols="12" md="6">
        <VTextField
          v-model="form.account_number"
          :label="t('finance.fields.account_number')"
          :error-messages="form.errors.account_number"
        />
      </VCol>

      <VCol cols="12" md="6">
        <VTextField
          v-model="form.iban"
          :label="t('finance.fields.iban')"
          :error-messages="form.errors.iban"
        />
      </VCol>
    </template>

    <VCol cols="12">
      <TranslatableTextarea
        v-model="form.instructions"
        field="instructions"
        :label="t('finance.fields.instructions')"
        :errors="form.errors"
      />
    </VCol>

    <VCol cols="12" md="6">
      <VTextField
        v-model.number="form.display_order"
        type="number"
        min="0"
        :label="t('finance.fields.display_order')"
        :error-messages="form.errors.display_order"
      />
    </VCol>

    <VCol cols="12" md="6">
      <VSwitch
        v-model="form.is_active"
        :label="t('finance.fields.is_active')"
        :error-messages="form.errors.is_active"
      />
    </VCol>
  </VRow>
</template>
