<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'

defineOptions({ layout: TeacherLayout })

defineProps({
  classes: { type: Array, default: () => [] },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('portal.nav.myClasses')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.myClasses')"
        :subtitle="t('portal.teacher.classesSubtitle')"
        icon="bx-chalkboard"
      />
    </VCol>

    <VCol
      v-for="item in classes"
      :key="item.id"
      cols="12"
      md="6"
    >
      <VCard>
        <VCardItem>
          <template #prepend>
            <VAvatar color="primary" variant="tonal" size="42">
              <VIcon icon="bx-chalkboard" />
            </VAvatar>
          </template>
          <VCardTitle>{{ item.name }}</VCardTitle>
          <VCardSubtitle>
            {{ item.grade_level ?? '—' }}
            <span v-if="item.section_code"> · {{ item.section_code }}</span>
          </VCardSubtitle>

          <template #append>
            <VChip size="small" variant="tonal">
              {{ item.students_count }}
            </VChip>
          </template>
        </VCardItem>

        <VDivider />

        <VList v-if="item.students.length" density="compact" max-height="260" class="overflow-y-auto">
          <VListItem
            v-for="student in item.students"
            :key="student.id"
          >
            <VListItemTitle>{{ student.name }}</VListItemTitle>
            <VListItemSubtitle>{{ student.admission_number ?? '—' }}</VListItemSubtitle>
          </VListItem>
        </VList>

        <VCardText v-else class="text-center text-disabled py-6">
          {{ t('portal.teacher.noStudents') }}
        </VCardText>

        <VDivider />

        <VCardText class="d-flex justify-end">
          <VBtn
            variant="tonal"
            size="small"
            prepend-icon="bx-list-check"
            :href="`/teacher/attendance?class_id=${item.id}`"
            :as="Link"
          >
            {{ t('portal.teacher.takeAttendance') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <VCol v-if="!classes.length" cols="12">
      <VCard>
        <VCardText class="d-flex flex-column align-center pa-12 text-center text-disabled">
          <VIcon icon="bx-chalkboard" size="48" class="mb-3" />
          {{ t('portal.teacher.noClasses') }}
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
