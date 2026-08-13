<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  /*
   * Named `schoolClass`, not `class`: Vue reserves `class` as an attribute
   * and normalizeClass() would flatten the object into a class string.
   */
  schoolClass: { type: Object, required: true },
  roster: { type: Array, default: () => [] },
})

const { t } = useI18n()

// Academic years are managed on a single index page (no per-item show
// route), so this action opens that index rather than a specific record.
const quickActions = [
  { key: 'edit', icon: 'bx-edit', href: `/academic/classes/${props.schoolClass.id}/edit` },
]
</script>

<template>
  <Head :title="$props.schoolClass.name" />

  <VRow>
    <!-- 👉 Header banner -->
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar color="primary" variant="tonal" size="56">
            <VIcon icon="bx-chalkboard" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ $props.schoolClass.name }}
              </h5>
              <VChip :color="$props.schoolClass.is_active ? 'success' : 'secondary'" size="small">
                {{ $props.schoolClass.is_active ? t('classes.active') : t('classes.inactive') }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ $props.schoolClass.grade_level?.name ?? '—' }}
            </p>
          </div>

          <VBtn
            prepend-icon="bx-edit"
            :href="`/academic/classes/${$props.schoolClass.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Class Details -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-chalkboard" />
          </template>
          <VCardTitle>{{ t('classes.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('classes.gradeLevel') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ $props.schoolClass.grade_level?.name ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('classes.classroom') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ $props.schoolClass.classroom?.name ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="12">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('classes.capacity') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ $props.schoolClass.capacity }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Enrolled Students -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-group" />
          </template>
          <VCardTitle>{{ t('classes.roster') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <div v-if="!roster || roster.length === 0" class="text-disabled">
            {{ t('classes.noStudents') }}
          </div>
          <VList v-else class="pa-0">
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

    <!-- 👉 Quick Actions -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-bolt-circle" />
          </template>
          <VCardTitle>{{ t('classes.quickActions') }}</VCardTitle>
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
            <VListItemTitle>{{ t(`classes.quickAction.${action.key}`) }}</VListItemTitle>
          </VListItem>
        </VList>
      </VCard>
    </VCol>
  </VRow>
</template>
