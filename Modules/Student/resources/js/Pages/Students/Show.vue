<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  student: { type: Object, required: true },
})

const { t } = useI18n()

const emptySections = [
  { key: 'attendance', icon: 'bx-calendar-check' },
  { key: 'results', icon: 'bx-medal' },
  { key: 'invoices', icon: 'bx-receipt' },
  { key: 'behavior', icon: 'bx-shield-quarter' },
  { key: 'transport', icon: 'bx-bus' },
  { key: 'library', icon: 'bx-book' },
]
</script>

<template>
  <Head :title="student.name" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ student.name }}</VCardTitle>
          <template #append>
            <VChip :color="student.is_active ? 'success' : 'secondary'" size="small">
              {{ student.is_active ? t('students.active') : t('students.inactive') }}
            </VChip>
            <IconBtn :href="`/user-management/students/${student.id}/edit`" :as="Link" class="ms-2">
              <VIcon icon="bx-edit" />
            </IconBtn>
          </template>
        </VCardItem>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem><VCardTitle>{{ t('students.personalInfo') }}</VCardTitle></VCardItem>
        <VCardText>
          <VRow>
            <VCol cols="6"><strong>{{ t('students.email') }}:</strong> {{ student.email }}</VCol>
            <VCol cols="6"><strong>{{ t('students.username') }}:</strong> {{ student.username }}</VCol>
            <VCol cols="6"><strong>{{ t('students.gender') }}:</strong> {{ student.gender === 'male' ? t('students.male') : t('students.female') }}</VCol>
            <VCol cols="6"><strong>{{ t('students.birthDate') }}:</strong> {{ student.birth_date ?? '—' }}</VCol>
            <VCol cols="6"><strong>{{ t('students.nationalId') }}:</strong> {{ student.national_id ?? '—' }}</VCol>
            <VCol cols="6"><strong>{{ t('students.phone') }}:</strong> {{ student.phone ?? '—' }}</VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem><VCardTitle>{{ t('students.academicInfo') }}</VCardTitle></VCardItem>
        <VCardText>
          <VRow>
            <VCol cols="6"><strong>{{ t('students.academicYear') }}:</strong> {{ student.academic_year?.name ?? '—' }}</VCol>
            <VCol cols="6">
              <strong>{{ t('students.currentClass') }}:</strong>
              {{ student.current_class ? `${student.current_class.name} ${student.current_class.section_code}` : '—' }}
            </VCol>
            <VCol cols="6"><strong>{{ t('students.admissionNumber') }}:</strong> {{ student.admission_number ?? '—' }}</VCol>
            <VCol cols="6"><strong>{{ t('students.admissionDate') }}:</strong> {{ student.admission_date }}</VCol>
            <VCol cols="6"><strong>{{ t('students.rollNumber') }}:</strong> {{ student.roll_number ?? '—' }}</VCol>
            <VCol cols="6"><strong>{{ t('students.scholarshipPercentage') }}:</strong> {{ student.scholarship_percentage }}%</VCol>
            <VCol cols="6"><strong>{{ t('students.isGraduated') }}:</strong> {{ student.is_graduated ? '✓' : '—' }}</VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem><VCardTitle>{{ t('students.guardiansTitle') }}</VCardTitle></VCardItem>
        <VCardText>
          <div v-if="!student.guardians || student.guardians.length === 0" class="text-disabled">
            {{ t('students.noGuardians') }}
          </div>
          <VList v-else>
            <VListItem
              v-for="guardian in student.guardians"
              :key="guardian.id"
            >
              <VListItemTitle>{{ guardian.name }} ({{ guardian.relationship }})</VListItemTitle>
              <VListItemSubtitle>{{ guardian.phone }}</VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem><VCardTitle>{{ t('students.sections.medical') }}</VCardTitle></VCardItem>
        <VCardText>
          <VRow>
            <VCol cols="6"><strong>{{ t('students.bloodGroup') }}:</strong> {{ student.blood_group ?? '—' }}</VCol>
            <VCol cols="6"><strong>{{ t('students.allergies') }}:</strong> {{ student.allergies ?? '—' }}</VCol>
            <VCol cols="12"><strong>{{ t('students.medicalConditions') }}:</strong> {{ student.medical_conditions ?? '—' }}</VCol>
            <VCol cols="12"><strong>{{ t('students.emergencyContactName') }}:</strong> {{ student.emergency_contact_name ?? '—' }} ({{ student.emergency_contact_phone ?? '—' }})</VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      v-for="section in emptySections"
      :key="section.key"
      cols="12"
      md="4"
    >
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon :icon="section.icon" />
          </template>
          <VCardTitle>{{ t(`students.${section.key}`) }}</VCardTitle>
        </VCardItem>
        <VCardText class="text-disabled">
          {{ t('students.notAvailableYet') }}
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
