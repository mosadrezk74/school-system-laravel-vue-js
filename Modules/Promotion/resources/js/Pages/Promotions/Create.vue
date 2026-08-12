<script setup>
/**
 * The bulk promotion wizard.
 *
 * Nothing is committed until the final confirm: choosing a year or a class
 * only reloads the eligible list. The source academic year is never sent from
 * here — the server reads it from the academic context — so the form carries
 * only the target year and the per-student decisions.
 */
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'

const props = defineProps({
  students: { type: Array, default: () => [] },
  targetClasses: { type: Array, default: () => [] },
  sourceClasses: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  statusOptions: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  context: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const targetYearId = ref(props.filters.to_academic_year_id ?? null)
const sourceClassId = ref(props.filters.class_id ?? null)

/** Reloads eligibility whenever the year or source class changes. */
const reload = () => {
  router.get('/user-management/promotions/create', {
    ...(targetYearId.value ? { to_academic_year_id: targetYearId.value } : {}),
    ...(sourceClassId.value ? { class_id: sourceClassId.value } : {}),
  }, { preserveState: true, preserveScroll: true, replace: true })
}

watch(targetYearId, reload)
watch(sourceClassId, reload)

/** Per-student decision state, keyed by student id. */
const decisions = ref({})

const selectedIds = computed(() => Object.keys(decisions.value).map(Number))

const isSelected = id => Object.hasOwn(decisions.value, id)

function toggle(student) {
  if (isSelected(student.student_id)) {
    const next = { ...decisions.value }

    delete next[student.student_id]
    decisions.value = next

    return
  }

  decisions.value = {
    ...decisions.value,
    [student.student_id]: { status: 'promoted', target_class_id: null, remarks: null },
  }
}

function selectAll() {
  const next = {}

  props.students.forEach(student => {
    next[student.student_id] = decisions.value[student.student_id]
      ?? { status: 'promoted', target_class_id: null, remarks: null }
  })

  decisions.value = next
}

function clearAll() {
  decisions.value = {}
}

/** Only a promotion needs a target class; the other decisions do not. */
const needsClass = id => decisions.value[id]?.status === 'promoted'

const classOptions = computed(() => props.targetClasses.map(item => ({
  value: item.class_id,
  title: `${item.class_name} — ${item.grade_level_name} (${item.available}/${item.capacity})`,
  disabled: item.available <= 0,
})))

const promotedCount = computed(
  () => selectedIds.value.filter(id => decisions.value[id].status === 'promoted').length,
)

/** The target year is only required when someone is actually being moved. */
const requiresTargetYear = computed(() => promotedCount.value > 0)

const missingClass = computed(
  () => selectedIds.value.some(id => needsClass(id) && !decisions.value[id].target_class_id),
)

const canSubmit = computed(() => selectedIds.value.length > 0
  && !missingClass.value
  && (!requiresTargetYear.value || Boolean(targetYearId.value)))

const form = useForm({ to_academic_year_id: null, decisions: [] })

const isConfirmOpen = ref(false)

function submit() {
  form.to_academic_year_id = targetYearId.value
  form.decisions = selectedIds.value.map(id => ({
    student_id: id,
    status: decisions.value[id].status,
    target_class_id: decisions.value[id].target_class_id,
    remarks: decisions.value[id].remarks,
  }))

  form.post('/user-management/promotions', {
    onSuccess: () => { decisions.value = {} },
  })
}

const dash = value => (value === null || value === undefined ? '—' : value)
</script>

<template>
  <Head :title="t('promotions.startPromotion')" />

  <AppPageHeader
    :title="t('promotions.startPromotion')"
    :subtitle="t('promotions.wizardSubtitle', { year: context.academic_year_name ?? '' })"
  />

  <VCard class="mb-4">
    <VCardText>
      <VRow dense>
        <VCol
          cols="12"
          md="4"
        >
          <VSelect
            v-model="targetYearId"
            :items="academicYears"
            :label="t('promotions.fields.toYear')"
            :hint="t('promotions.hints.targetYear')"
            persistent-hint
            clearable
          />
        </VCol>

        <VCol
          cols="12"
          md="4"
        >
          <VSelect
            v-model="sourceClassId"
            :items="[{ title: t('common.all'), value: null }, ...sourceClasses]"
            :label="t('promotions.fields.fromClass')"
          />
        </VCol>

        <VCol
          cols="12"
          md="4"
          class="d-flex align-center gap-2"
        >
          <VBtn
            size="small"
            variant="tonal"
            :disabled="!students.length"
            @click="selectAll"
          >
            {{ t('promotions.selectAll') }}
          </VBtn>
          <VBtn
            size="small"
            variant="tonal"
            color="secondary"
            :disabled="!selectedIds.length"
            @click="clearAll"
          >
            {{ t('promotions.clearAll') }}
          </VBtn>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>

  <AppEmptyState
    v-if="!students.length"
    :title="t('promotions.noEligible')"
    :description="t('promotions.noEligibleHint')"
    icon="bx-user-check"
  />

  <template v-else>
    <VCard>
      <VTable>
        <thead>
          <tr>
            <th />
            <th>{{ t('promotions.fields.student') }}</th>
            <th>{{ t('promotions.fields.currentClass') }}</th>
            <th>{{ t('promotions.fields.average') }}</th>
            <th>{{ t('promotions.fields.status') }}</th>
            <th>{{ t('promotions.fields.toClass') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="student in students"
            :key="student.student_id"
          >
            <td>
              <VCheckbox
                :model-value="isSelected(student.student_id)"
                hide-details
                density="compact"
                @click="toggle(student)"
              />
            </td>
            <td>
              {{ student.student_name }}
              <div class="text-caption text-medium-emphasis">
                {{ dash(student.admission_number) }}
              </div>
            </td>
            <td>
              {{ dash(student.current_class_name) }}
              <div class="text-caption text-medium-emphasis">
                {{ dash(student.grade_level_name) }}
              </div>
            </td>
            <td>
              <template v-if="student.has_results">
                {{ student.average_percentage }}%
                <VChip
                  v-if="student.overall_grade"
                  size="x-small"
                  class="ms-1"
                >
                  {{ student.overall_grade }}
                </VChip>
              </template>
              <span v-else class="text-medium-emphasis">{{ t('promotions.noResults') }}</span>
            </td>
            <td style="min-inline-size: 160px;">
              <VSelect
                v-if="isSelected(student.student_id)"
                v-model="decisions[student.student_id].status"
                :items="statusOptions"
                density="compact"
                hide-details
              />
              <span v-else>—</span>
            </td>
            <td style="min-inline-size: 220px;">
              <VSelect
                v-if="isSelected(student.student_id) && needsClass(student.student_id)"
                v-model="decisions[student.student_id].target_class_id"
                :items="classOptions"
                :no-data-text="t('promotions.noTargetClasses')"
                density="compact"
                hide-details
              />
              <span v-else>—</span>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <VCard class="mt-4">
      <VCardText class="d-flex flex-wrap align-center justify-space-between gap-3">
        <div>
          <p class="text-body-1 mb-0">
            {{ t('promotions.selectedSummary', { selected: selectedIds.length, promoted: promotedCount }) }}
          </p>
          <p
            v-if="missingClass"
            class="text-body-2 text-error mb-0"
          >
            {{ t('promotions.missingClass') }}
          </p>
          <p
            v-else-if="requiresTargetYear && !targetYearId"
            class="text-body-2 text-error mb-0"
          >
            {{ t('promotions.missingTargetYear') }}
          </p>
        </div>

        <div class="d-flex gap-2">
          <VBtn
            variant="tonal"
            color="secondary"
            href="/user-management/promotions"
            :as="Link"
          >
            {{ t('common.cancel') }}
          </VBtn>
          <VBtn
            color="primary"
            :disabled="!canSubmit || form.processing"
            :loading="form.processing"
            @click="isConfirmOpen = true"
          >
            {{ t('promotions.review') }}
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </template>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :message="t('promotions.confirmBulk', { count: selectedIds.length, promoted: promotedCount })"
    :confirm-text="t('promotions.confirmExecute')"
    color="primary"
    @confirm="submit"
  />
</template>
