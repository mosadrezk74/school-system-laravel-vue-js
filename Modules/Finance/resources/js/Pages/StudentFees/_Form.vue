<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'

const props = defineProps({
  form: { type: Object, required: true },
  students: { type: Array, default: () => [] },
  feeStructures: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
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
const statusOptions = computed(() => props.statuses.map(s => ({ title: s.label, value: s.value })))

/*
 * Mirrors CreateStudentFeeDTO::finalAmount() so the figure shown while typing
 * is the one that will be stored; the server still recomputes it on save.
 */
const finalAmount = computed(() => {
  const amount = Number(props.form.amount) || 0
  const discount = Number(props.form.discount_amount) || 0

  return Math.max(0, Number((amount - discount).toFixed(2)))
})

// Picking a structure pre-fills its list price; the amount stays editable so a
// one-off figure can still be entered for a single student.
watch(() => props.form.fee_structure_id, id => {
  if (props.isEdit || !id)
    return

  const structure = props.feeStructures.find(f => f.id === id)

  if (structure)
    props.form.amount = structure.amount
})

const activeTab = ref('details')

const tabFields = {
  details: ['student_id', 'fee_structure_id', 'amount', 'discount_amount', 'status'],
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
      <VIcon size="20" start icon="bx-user-check" />
      {{ t('finance.sections.details') }}
      <VIcon v-if="tabHasError('details')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="details">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-user-check" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ t('finance.studentFees.sectionTitle') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ t('finance.studentFees.sectionDesc') }}
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
          <VSelect
            v-model="form.fee_structure_id"
            :items="feeStructureOptions"
            :label="required(t('finance.fields.fee_structure_id'))"
            :hint="t('finance.hints.feeStructure')"
            persistent-hint
            :error-messages="form.errors.fee_structure_id"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.amount"
            inputmode="decimal"
            max="99999999.99"
            type="number"
            step="0.01"
            min="0"
            :label="required(t('finance.fields.amount'))"
            :hint="t('finance.hints.feeAmount')"
            persistent-hint
            :error-messages="form.errors.amount"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.discount_amount"
            inputmode="decimal"
            max="99999999.99"
            type="number"
            step="0.01"
            min="0"
            :label="t('finance.fields.discount_amount')"
            :hint="t('finance.hints.discountAmount')"
            persistent-hint
            :error-messages="form.errors.discount_amount"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.status"
            :items="statusOptions"
            :label="t('finance.fields.status')"
            :hint="t('finance.hints.status')"
            persistent-hint
            :error-messages="form.errors.status"
          />
        </VCol>

        <VCol cols="12">
          <VAlert type="success" variant="tonal" density="compact">
            {{ t('finance.finalAmount') }}: <strong>{{ formatMoney(finalAmount) }}</strong>
          </VAlert>
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>
