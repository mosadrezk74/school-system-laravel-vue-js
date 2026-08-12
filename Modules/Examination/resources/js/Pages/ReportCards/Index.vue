<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import ReportCardView from '@modules/Examination/resources/js/components/ReportCardView.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

const props = defineProps({
  card: { type: Object, default: null },
  students: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const classId = ref(props.filters.class_id ?? null)
const studentId = ref(props.filters.student_id ?? null)

/*
 * Only the student and class travel in the query string; the academic year and
 * semester are resolved server-side from the academic context and are never
 * sent from here.
 */
const visit = () => {
  router.get('/examination/report-cards', {
    ...(classId.value ? { class_id: classId.value } : {}),
    ...(studentId.value ? { student_id: studentId.value } : {}),
  }, { preserveState: true, preserveScroll: true, replace: true })
}

// Changing class narrows the student list, so the previous pick is dropped.
watch(classId, () => {
  studentId.value = null
  visit()
})

watch(studentId, visit)

const classOptions = computed(() => [
  { title: t('common.all'), value: null },
  ...props.classes,
])
</script>

<template>
  <Head :title="t('reportCard.title')" />

  <AppPageHeader
    :title="t('reportCard.title')"
    :subtitle="t('reportCard.subtitle')"
  />

  <VCard class="mb-4">
    <VCardText>
      <VRow dense>
        <VCol
          cols="12"
          md="4"
        >
          <VSelect
            v-model="classId"
            :items="classOptions"
            :label="t('reportCard.fields.class')"
          />
        </VCol>

        <VCol
          cols="12"
          md="5"
        >
          <VSelect
            v-model="studentId"
            :items="students"
            :label="t('reportCard.fields.student')"
            :no-data-text="t('reportCard.noStudents')"
            clearable
          />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>

  <ReportCardView
    v-if="card"
    :card="card"
    show-publish-state
  />

  <AppEmptyState
    v-else
    :title="t('reportCard.selectStudent')"
    :description="t('reportCard.selectStudentHint')"
    icon="bx-medal"
  />
</template>
