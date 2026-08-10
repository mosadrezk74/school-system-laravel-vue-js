<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  form: { type: Object, required: true },
  students: { type: Array, default: () => [] },
  feeStructures: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

const { t } = useI18n()
const { formatMoney } = useMoney()

const required = label => `${label} *`

const studentOptions = computed(() => props.students.map(s => ({
  title: s.admission_number ? `${s.name} (${s.admission_number})` : s.name,
  value: s.id,
})))

const feeStructureOptions = computed(() => props.feeStructures.map(f => ({ title: f.name, value: f.id })))

/*
 * Mirrors InvoiceRepository::syncTotals() so the figures shown while editing
 * match what the server will store once the invoice is saved.
 */
const lineTotal = item => {
  const quantity = Number(item.quantity) || 0
  const unitPrice = Number(item.unit_price) || 0
  const discount = Number(item.discount_amount) || 0

  return Math.max(0, Number(((quantity * unitPrice) - discount).toFixed(2)))
}

const subtotal = computed(() => Number(props.form.items
  .reduce((sum, item) => sum + ((Number(item.quantity) || 0) * (Number(item.unit_price) || 0)), 0)
  .toFixed(2)))

const discountTotal = computed(() => Number(props.form.items
  .reduce((sum, item) => sum + (Number(item.discount_amount) || 0), 0)
  .toFixed(2)))

const grandTotal = computed(() => Math.max(0, Number((subtotal.value - discountTotal.value).toFixed(2))))

function addItem() {
  props.form.items.push({
    fee_structure_id: null,
    quantity: 1,
    unit_price: '',
    discount_amount: '',
  })
}

function removeItem(index) {
  props.form.items.splice(index, 1)
}

function onStructureChange(item, id) {
  const structure = props.feeStructures.find(f => f.id === id)

  if (structure && !item.unit_price)
    item.unit_price = structure.amount
}

function itemError(index, field) {
  return props.form.errors[`items.${index}.${field}`]
}

const activeTab = ref('details')

const tabFields = {
  details: ['student_id', 'invoice_number', 'issue_date', 'due_date', 'currency', 'notes'],
  items: ['items'],
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
      <VIcon size="20" start icon="bx-detail" />
      {{ t('finance.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="items">
      <VIcon size="20" start icon="bx-list-ul" />
      {{ t('finance.sections.items') }}
      <VIcon v-if="tabHasError('items')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="details">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-detail" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('finance.sections.details') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('finance.invoices.sectionDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol v-if="!isEdit" cols="12" md="6">
          <VSelect
            v-model="form.student_id"
            :items="studentOptions"
            :label="required(t('finance.fields.student_id'))"
            :hint="t('finance.hints.student')"
            persistent-hint
            :error-messages="form.errors.student_id"
            required
          />
        </VCol>

        <VCol v-if="!isEdit" cols="12" md="6">
          <VTextField
            v-model="form.invoice_number"
            :label="t('finance.fields.invoice_number')"
            :hint="t('finance.hints.invoiceNumber')"
            persistent-hint
            :error-messages="form.errors.invoice_number"
          />
        </VCol>

        <VCol cols="12" md="4">
          <AppDateField
            v-model="form.issue_date"
            :label="required(t('finance.fields.issue_date'))"
            :hint="t('finance.hints.issueDate')"
            persistent-hint
            :error-messages="form.errors.issue_date"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <AppDateField
            v-model="form.due_date"
            :label="required(t('finance.fields.due_date'))"
            :hint="t('finance.hints.dueDate2')"
            persistent-hint
            :error-messages="form.errors.due_date"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.currency"
            maxlength="3"
            :counter="3"
            :label="t('finance.fields.currency')"
            :hint="t('finance.hints.currency')"
            persistent-hint
            :error-messages="form.errors.currency"
          />
        </VCol>

        <VCol cols="12">
          <VTextarea
            v-model="form.notes"
            rows="3"
            :label="t('finance.fields.notes')"
            :hint="t('finance.hints.notes')"
            persistent-hint
            :error-messages="form.errors.notes"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="items">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-list-ul" />
        </VAvatar>
        <div class="flex-grow-1">
          <h6 class="text-h6">
            {{ t('finance.sections.items') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('finance.invoices.itemsDesc') }}
          </p>
        </div>
        <VBtn
          variant="tonal"
          prepend-icon="bx-plus"
          @click="addItem"
        >
          {{ t('finance.invoices.addItem') }}
        </VBtn>
      </div>

      <VAlert
        v-if="form.errors.items"
        type="error"
        variant="tonal"
        density="compact"
        class="mb-4"
      >
        {{ form.errors.items }}
      </VAlert>

      <VCard
        v-for="(item, index) in form.items"
        :key="index"
        variant="outlined"
        class="mb-4"
      >
        <VCardText>
          <VRow align="center">
            <VCol cols="12" md="4">
              <VSelect
                v-model="item.fee_structure_id"
                :items="feeStructureOptions"
                :label="required(t('finance.fields.fee_structure_id'))"
                density="compact"
                :error-messages="itemError(index, 'fee_structure_id')"
                @update:model-value="value => onStructureChange(item, value)"
              />
            </VCol>

            <VCol cols="6" md="2">
              <VTextField
                v-model="item.quantity"
                type="number"
                min="1"
                :label="t('finance.quantity')"
                density="compact"
                :error-messages="itemError(index, 'quantity')"
              />
            </VCol>

            <VCol cols="6" md="2">
              <VTextField
                v-model="item.unit_price"
                type="number"
                step="0.01"
                min="0"
                :label="required(t('finance.unitPrice'))"
                density="compact"
                :error-messages="itemError(index, 'unit_price')"
              />
            </VCol>

            <VCol cols="6" md="2">
              <VTextField
                v-model="item.discount_amount"
                type="number"
                step="0.01"
                min="0"
                :label="t('finance.fields.discount_amount')"
                density="compact"
                :error-messages="itemError(index, 'discount_amount')"
              />
            </VCol>

            <VCol cols="6" md="2" class="d-flex align-center justify-space-between">
              <div>
                <p class="text-caption text-medium-emphasis mb-0">
                  {{ t('finance.lineTotal') }}
                </p>
                <strong>{{ formatMoney(lineTotal(item), form.currency) }}</strong>
              </div>
              <IconBtn @click="removeItem(index)">
                <VIcon icon="bx-trash" color="error" />
              </IconBtn>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <AppEmptyState v-if="!form.items.length"
          icon="bx-list-ul"
          :title="t('finance.invoices.noItems')"
        />

      <VCard v-else variant="tonal" color="primary">
        <VCardText>
          <div class="d-flex justify-space-between mb-2">
            <span>{{ t('finance.fields.subtotal') }}</span>
            <span>{{ formatMoney(subtotal, form.currency) }}</span>
          </div>
          <div class="d-flex justify-space-between mb-2">
            <span>{{ t('finance.fields.discount_amount') }}</span>
            <span>− {{ formatMoney(discountTotal, form.currency) }}</span>
          </div>
          <VDivider class="my-2" />
          <div class="d-flex justify-space-between text-h6">
            <strong>{{ t('finance.fields.total_amount') }}</strong>
            <strong>{{ formatMoney(grandTotal, form.currency) }}</strong>
          </div>
        </VCardText>
      </VCard>
    </VWindowItem>
  </VWindow>
</template>
