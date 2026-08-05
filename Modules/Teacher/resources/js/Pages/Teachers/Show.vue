<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  teacher: { type: Object, required: true },
})

const { t } = useI18n()

const quickActions = [
  { key: 'edit', icon: 'bx-edit', href: `/user-management/teachers/${props.teacher.id}/edit` },
]
</script>

<template>
  <Head :title="teacher.name" />

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
                {{ teacher.name }}
              </h5>
              <VChip :color="teacher.is_active ? 'success' : 'secondary'" size="small">
                {{ teacher.is_active ? t('common.active') : t('common.inactive') }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ teacher.employee_id ?? '—' }} · {{ teacher.position ?? '—' }}
              <template v-if="teacher.department">
                · {{ teacher.department.name }}
              </template>
            </p>
          </div>

          <VBtn
            prepend-icon="bx-edit"
            :href="`/user-management/teachers/${teacher.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Account Details -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-user" />
          </template>
          <VCardTitle>{{ t('teacher.sections.account') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.email') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.email }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.username') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.username }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.gender') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.gender === 'male' ? t('students.male') : t('students.female') }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('students.birthDate') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.birth_date ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('students.nationalId') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.national_id ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('students.phone') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.phone ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Professional Information -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-briefcase" />
          </template>
          <VCardTitle>{{ t('teacher.sections.professional') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.employee_id') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.employee_id ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.qualification') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.qualification ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.specialization') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.specialization ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.experience_years') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.experience_years ?? '0' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="12">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.department_id') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.department?.name ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Employment Data -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-money" />
          </template>
          <VCardTitle>{{ t('teacher.sections.employment') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.joining_date') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.joining_date ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.position') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.position ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.salary_type') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.salary_type ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('teacher.fields.salary_base') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ teacher.salary_base ? `$${teacher.salary_base}` : '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Subjects -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-book-open" />
          </template>
          <VCardTitle>{{ t('teacher.sections.subjects') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <div v-if="!teacher.primary_subjects || teacher.primary_subjects.length === 0" class="text-disabled">
            {{ t('common.noItems') }}
          </div>
          <VChip
            v-else
            v-for="subject in teacher.primary_subjects"
            :key="subject"
            class="ma-1"
          >
            {{ subject }}
          </VChip>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Quick Actions -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-bolt-circle" />
          </template>
          <VCardTitle>{{ t('teacher.quickActions') }}</VCardTitle>
        </VCardItem>
        <VList density="compact" class="pa-0">
          <VListItem
            v-for="action in quickActions"
            :key="action.key"
            :disabled="!action.href"
            :href="action.href"
            :as="action.href ? Link : 'div'"
          >
            <template #prepend>
              <VIcon :icon="action.icon" size="20" />
            </template>
            <VListItemTitle>{{ t(`teacher.quickAction.${action.key}`) }}</VListItemTitle>
          </VListItem>
        </VList>
      </VCard>
    </VCol>
  </VRow>
</template>
