<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  class: { type: Object, required: true },
  roster: { type: Array, default: () => [] },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="`${$props.class.name} ${$props.class.section_code}`" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ $props.class.name }} {{ $props.class.section_code }}</VCardTitle>
          <template #append>
            <VChip :color="$props.class.is_active ? 'success' : 'secondary'" size="small">
              {{ $props.class.is_active ? t('classes.active') : t('classes.inactive') }}
            </VChip>
            <IconBtn :href="`/academic/classes/${$props.class.id}/edit`" :as="Link" class="ms-2">
              <VIcon icon="bx-edit" />
            </IconBtn>
          </template>
        </VCardItem>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem><VCardTitle>{{ t('classes.details') }}</VCardTitle></VCardItem>
        <VCardText>
          <VRow>
            <VCol cols="6"><strong>{{ t('classes.gradeLevel') }}:</strong> {{ $props.class.grade_level?.name ?? '—' }}</VCol>
            <VCol cols="6"><strong>{{ t('classes.academicYear') }}:</strong> {{ $props.class.academic_year?.name ?? '—' }}</VCol>
            <VCol cols="6"><strong>{{ t('classes.branch') }}:</strong> {{ $props.class.branch?.name ?? '—' }}</VCol>
            <VCol cols="6"><strong>{{ t('classes.classroom') }}:</strong> {{ $props.class.classroom?.name ?? '—' }}</VCol>
            <VCol cols="6"><strong>{{ t('classes.classTeacher') }}:</strong> {{ $props.class.class_teacher?.name ?? '—' }}</VCol>
            <VCol cols="6"><strong>{{ t('classes.assistantTeacher') }}:</strong> {{ $props.class.assistant_teacher?.name ?? '—' }}</VCol>
            <VCol cols="12"><strong>{{ t('classes.capacity') }}:</strong> {{ $props.class.current_enrollment }} / {{ $props.class.capacity }}</VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem><VCardTitle>{{ t('classes.roster') }}</VCardTitle></VCardItem>
        <VCardText>
          <div v-if="!roster || roster.length === 0" class="text-disabled">
            {{ t('classes.noStudents') }}
          </div>
          <VList v-else>
            <VListItem
              v-for="student in roster"
              :key="student.id"
              :href="`/user-management/students/${student.id}`"
              :as="Link"
            >
              <VListItemTitle>{{ student.name }}</VListItemTitle>
              <VListItemSubtitle>{{ t('classes.admissionNumber') }}: {{ student.admission_number ?? '—' }}</VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
