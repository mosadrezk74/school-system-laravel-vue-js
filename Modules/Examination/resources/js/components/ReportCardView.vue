<script setup>
/**
 * The report card itself, shared by the admin screen and both portals.
 *
 * Purely presentational: every figure — subject totals, average, grade, rank —
 * is computed by ReportCardQuery on the server. Nothing is recalculated here,
 * so the admin and the family always read identical numbers for the same data.
 */
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppDataTable from '@/components/AppDataTable.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

const props = defineProps({
  card: { type: Object, required: true },

  /** Staff see whether each result comes from an unpublished exam. */
  showPublishState: { type: Boolean, default: false },
})

const { t } = useI18n()

const headers = computed(() => [
  { title: t('reportCard.fields.subject'), key: 'subject_name', sortable: false },
  { title: t('reportCard.fields.obtained'), key: 'obtained_marks', sortable: false, align: 'end' },
  { title: t('reportCard.fields.max'), key: 'max_marks', sortable: false, align: 'end' },
  { title: t('reportCard.fields.percentage'), key: 'percentage', sortable: false, align: 'end' },
  { title: t('reportCard.fields.grade'), key: 'grade', sortable: false },
  { title: t('reportCard.fields.result'), key: 'is_passed', sortable: false },
])

const subjects = computed(() => props.card.subjects ?? [])

const dash = value => (value === null || value === undefined ? '—' : value)

const percent = value => (value === null || value === undefined ? '—' : `${value}%`)

/** Every unpublished sitting behind this subject, for the staff-only hint. */
const unpublishedCount = subject =>
  (subject.results ?? []).filter(result => !result.is_published).length
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="3"
            >
              <p class="text-caption text-medium-emphasis mb-1">
                {{ t('reportCard.fields.student') }}
              </p>
              <p class="text-body-1 font-weight-medium mb-0">
                {{ card.student_name }}
              </p>
              <p
                v-if="card.admission_number"
                class="text-caption text-medium-emphasis mb-0"
              >
                {{ card.admission_number }}
              </p>
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <p class="text-caption text-medium-emphasis mb-1">
                {{ t('reportCard.fields.class') }}
              </p>
              <p class="text-body-1 mb-0">
                {{ dash(card.class_name) }}
              </p>
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <p class="text-caption text-medium-emphasis mb-1">
                {{ t('reportCard.fields.academicYear') }}
              </p>
              <p class="text-body-1 mb-0">
                {{ dash(card.academic_year_name) }}
              </p>
            </VCol>

            <VCol
              cols="12"
              md="3"
            >
              <p class="text-caption text-medium-emphasis mb-1">
                {{ t('reportCard.fields.semester') }}
              </p>
              <p class="text-body-1 mb-0">
                {{ dash(card.semester_name) }}
              </p>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <AppEmptyState
        v-if="!subjects.length"
        :title="t('reportCard.empty')"
        :description="t('reportCard.emptyHint')"
        icon="bx-medal"
      />

      <template v-else>
        <AppDataTable
          :headers="headers"
          :items="subjects"
          item-key="subject_id"
          mobile-title-key="subject_name"
          :empty-title="t('reportCard.empty')"
          empty-icon="bx-medal"
        >
          <template #item.subject_name="{ item }">
            {{ item.subject_name }}
            <VChip
              v-if="showPublishState && unpublishedCount(item) > 0"
              size="x-small"
              color="warning"
              class="ms-2"
            >
              {{ t('reportCard.unpublishedCount', { count: unpublishedCount(item) }) }}
            </VChip>
          </template>

          <template #item.obtained_marks="{ item }">
            {{ dash(item.obtained_marks) }}
          </template>

          <template #item.max_marks="{ item }">
            {{ dash(item.max_marks) }}
          </template>

          <template #item.percentage="{ item }">
            {{ percent(item.percentage) }}
          </template>

          <template #item.grade="{ item }">
            <VChip
              v-if="item.grade"
              size="small"
            >
              {{ item.grade }}
            </VChip>
            <span v-else>—</span>
          </template>

          <template #item.is_passed="{ item }">
            <VChip
              v-if="item.is_passed !== null"
              :color="item.is_passed ? 'success' : 'error'"
              size="small"
            >
              {{ item.is_passed ? t('reportCard.passed') : t('reportCard.failed') }}
            </VChip>
            <span v-else>{{ t('reportCard.pending') }}</span>
          </template>
        </AppDataTable>

        <VCard class="mt-4">
          <VCardText>
            <VRow>
              <VCol
                cols="6"
                md="3"
              >
                <p class="text-caption text-medium-emphasis mb-1">
                  {{ t('reportCard.fields.total') }}
                </p>
                <p class="text-h6 mb-0">
                  {{ dash(card.total_obtained) }} / {{ dash(card.total_max) }}
                </p>
              </VCol>

              <VCol
                cols="6"
                md="3"
              >
                <p class="text-caption text-medium-emphasis mb-1">
                  {{ t('reportCard.fields.average') }}
                </p>
                <p class="text-h6 mb-0">
                  {{ percent(card.average_percentage) }}
                </p>
              </VCol>

              <VCol
                cols="6"
                md="3"
              >
                <p class="text-caption text-medium-emphasis mb-1">
                  {{ t('reportCard.fields.overallGrade') }}
                </p>
                <p class="text-h6 mb-0">
                  {{ dash(card.overall_grade) }}
                </p>
              </VCol>

              <VCol
                cols="6"
                md="3"
              >
                <p class="text-caption text-medium-emphasis mb-1">
                  {{ t('reportCard.fields.rank') }}
                </p>
                <p class="text-h6 mb-0">
                  <template v-if="card.class_rank">
                    {{ card.class_rank }}
                    <span class="text-body-2 text-medium-emphasis">
                      / {{ card.class_size }}
                    </span>
                  </template>
                  <template v-else>—</template>
                </p>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </template>
    </VCol>
  </VRow>
</template>
