<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  student: { type: Object, required: true },
  recentBehaviorRecords: { type: Array, default: () => [] },
})

const { t } = useI18n()

const emptySections = [
  { key: 'attendance', icon: 'bx-calendar-check' },
  { key: 'results', icon: 'bx-medal' },
  { key: 'invoices', icon: 'bx-receipt' },
  { key: 'transport', icon: 'bx-bus' },
  { key: 'library', icon: 'bx-book' },
]

const quickActions = [
  { key: 'edit', icon: 'bx-edit', href: `/user-management/students/${props.student.id}/edit` },
  { key: 'addBehaviorRecord', icon: 'bx-shield-quarter', href: `/behavior/records/create?student_id=${props.student.id}` },
  { key: 'viewClass', icon: 'bx-chalkboard', href: props.student.current_class ? `/academic/classes/${props.student.current_class.id}` : null },
]
</script>

<template>
  <Head :title="student.name" />

  <VRow>
    <!-- 👉 Header banner -->
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar color="primary" variant="tonal" size="56">
            <VIcon icon="bx-user" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ student.name }}
              </h5>
              <VChip :color="student.is_active ? 'success' : 'secondary'" size="small">
                {{ student.is_active ? t('students.active') : t('students.inactive') }}
              </VChip>
              <VChip v-if="student.is_graduated" color="info" size="small" variant="tonal">
                {{ t('students.isGraduated') }}
              </VChip>
            </div>
          </div>

          <VBtn
            prepend-icon="bx-edit"
            :href="`/user-management/students/${student.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Basic Information -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-id-card" />
          </template>
          <VCardTitle>{{ t('students.personalInfo') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('students.email') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ student.email }}
              </VListItemSubtitle>
            </VListItem>
            <VRow no-gutters>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('students.gender') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ student.gender === 'male' ? t('students.male') : t('students.female') }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('students.birthDate') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ student.birth_date ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('students.nationalId') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ student.national_id ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
              <VCol cols="6">
                <VListItem>
                  <VListItemTitle class="text-medium-emphasis text-caption">
                    {{ t('students.phone') }}
                  </VListItemTitle>
                  <VListItemSubtitle class="text-body-1 text-high-emphasis">
                    {{ student.phone ?? '—' }}
                  </VListItemSubtitle>
                </VListItem>
              </VCol>
            </VRow>
          </VList>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Academic Information -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-book-open" />
          </template>
          <VCardTitle>{{ t('students.academicInfo') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('students.academicYear') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ student.academic_year?.name ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('students.currentClass') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  <Link
                    v-if="student.current_class"
                    :href="`/academic/classes/${student.current_class.id}`"
                  >
                    {{ student.current_class.name }}
                  </Link>
                  <span v-else>—</span>
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('students.admissionNumber') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ student.admission_number ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('students.admissionDate') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ student.admission_date }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Guardians -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-group" />
          </template>
          <VCardTitle>{{ t('students.guardiansTitle') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <div v-if="!student.guardians || student.guardians.length === 0" class="text-disabled">
            {{ t('students.noGuardians') }}
          </div>
          <VList v-else class="pa-0">
            <VListItem
              v-for="guardian in student.guardians"
              :key="guardian.id"
            >
              <template #prepend>
                <VAvatar color="secondary" variant="tonal" size="36">
                  <VIcon icon="bx-user" size="20" />
                </VAvatar>
              </template>
              <VListItemTitle>
                <Link :href="`/user-management/guardians/${guardian.parent_id}`">
                  {{ guardian.name }}
                </Link>
                <span class="text-medium-emphasis">({{ guardian.relationship }})</span>
              </VListItemTitle>
              <VListItemSubtitle>{{ guardian.phone }}</VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>

  </VRow>
</template>
