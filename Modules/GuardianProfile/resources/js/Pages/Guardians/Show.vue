<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'

const props = defineProps({
  /** Sent by the controller from the same enum the rule validates against. */
  relationships: { type: Array, default: () => [] },
  guardian: { type: Object, required: true },
  students: { type: Object, default: () => ({ data: [], meta: {} }) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

/*
 * The picker queries the server as the admin types: the student table is far
 * too large to ship into the page, so only one page of matches is loaded.
 */
const studentSearch = ref('')

let searchTimer
watch(studentSearch, value => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get(`/user-management/guardians/${props.guardian.id}`, { student_search: value }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ['students'],
    })
  }, 350)
})

const linkedIds = computed(() => (props.guardian.students ?? []).map(row => row.student_id))

const studentOptions = computed(() => (props.students.data ?? []).map(student => ({
  title: [student.name, student.admission_number, student.class_name].filter(Boolean).join(' · '),
  value: student.id,
  disabled: linkedIds.value.includes(student.id),
})))

const linkForm = useForm({
  student_id: null,
  relationship: '',
  is_primary_contact: true,
  can_pickup: true,
  receives_notifications: true,
})

const isDuplicate = computed(() => linkForm.student_id !== null
  && linkedIds.value.includes(linkForm.student_id))

function submitLink() {
  if (isDuplicate.value)
    return

  linkForm.post(`/user-management/guardians/${props.guardian.id}/students`, {
    preserveScroll: true,
    onSuccess: () => linkForm.reset(),
  })
}

function unlink(student) {
  askConfirm({
    message: t('guardians.unlinkConfirm'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/user-management/guardians/${props.guardian.id}/students/${student.student_id}`, {
        preserveScroll: true,
      })
    },
  })
}

const primaryStudent = props.guardian.students?.[0] ?? null

const quickActions = [
  { key: 'edit', icon: 'bx-edit', href: `/user-management/guardians/${props.guardian.id}/edit` },
  { key: 'viewStudent', icon: 'bx-user', href: primaryStudent ? `/user-management/students/${primaryStudent.student_id}` : null },
]
</script>

<template>
  <Head :title="guardian.name" />

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
                {{ guardian.name }}
              </h5>
              <VChip :color="guardian.is_active ? 'success' : 'secondary'" size="small">
                {{ guardian.is_active ? t('guardians.active') : t('guardians.inactive') }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ guardian.email }} · {{ guardian.phone ?? '—' }}
            </p>
          </div>

          <VBtn
            prepend-icon="bx-edit"
            :href="`/user-management/guardians/${guardian.id}/edit`"
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
          <VCardTitle>{{ t('guardians.title') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('guardians.email') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ guardian.email }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('guardians.username') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ guardian.username }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('guardians.phone') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ guardian.phone ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('guardians.occupation') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ guardian.occupation ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('guardians.nationalId') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ guardian.national_id ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
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
          <VCardTitle>{{ t('guardians.quickActions') }}</VCardTitle>
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
            <VListItemTitle>{{ t(`guardians.quickAction.${action.key}`) }}</VListItemTitle>
          </VListItem>
        </VList>
      </VCard>
    </VCol>

    <!-- 👉 Linked Students -->
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-group" />
          </template>
          <VCardTitle>{{ t('guardians.linkedStudents') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <div v-if="!guardian.students || guardian.students.length === 0" class="text-disabled">
            {{ t('guardians.noLinkedStudents') }}
          </div>
          <VList v-else class="pa-0">
            <VListItem
              v-for="student in guardian.students"
              :key="student.id"
            >
              <template #prepend>
                <VAvatar color="secondary" variant="tonal" size="36">
                  <VIcon icon="bx-user" size="20" />
                </VAvatar>
              </template>
              <VListItemTitle>
                <Link :href="`/user-management/students/${student.student_id}`">
                  {{ student.name }}
                </Link>
                <span class="text-medium-emphasis"> ({{ student.admission_number }})</span>
              </VListItemTitle>
              <VListItemSubtitle class="d-flex align-center gap-2 mt-1">
                <VChip size="x-small">
                  {{ student.relationship }}
                </VChip>
                <VIcon
                  v-if="student.is_primary_contact"
                  icon="bx-star"
                  size="16"
                  color="warning"
                  :title="t('guardians.isPrimaryContact')"
                />
                <VIcon
                  v-if="student.can_pickup"
                  icon="bx-walk"
                  size="16"
                  color="success"
                  :title="t('guardians.canPickup')"
                />
              </VListItemSubtitle>

              <template #append>
                <IconBtn @click="unlink(student)">
                  <VIcon icon="bx-unlink" />
                </IconBtn>
              </template>
            </VListItem>
          </VList>
        </VCardText>

        <VDivider />

        <VCardText>
          <form @submit.prevent="submitLink">
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="studentSearch"
                  maxlength="100"
                  :label="t('guardians.searchStudents')"
                  prepend-inner-icon="bx-search"
                  clearable
                  :hint="t('guardians.searchStudentsHint', { total: students.meta?.total ?? 0 })"
                  persistent-hint
                />
              </VCol>

              <VCol cols="12" md="6">
                <VSelect
                  v-model="linkForm.student_id"
                  :items="studentOptions"
                  :label="t('guardians.linkStudent')"
                  :error-messages="linkForm.errors.student_id"
                  :no-data-text="t('guardians.noStudentsFound')"
                />
              </VCol>

              <VCol v-if="isDuplicate" cols="12">
                <VAlert type="warning" variant="tonal" density="compact">
                  {{ t('guardians.alreadyLinked') }}
                </VAlert>
              </VCol>

              <VCol cols="12" md="6">
                <VSelect
                  v-model="linkForm.relationship"
                  :items="relationships"
                  :label="t('guardians.relationship')"
                  :hint="t('guardians.hints.relationship')"
                  persistent-hint
                  :error-messages="linkForm.errors.relationship"
                />
              </VCol>

              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VSwitch
                  v-model="linkForm.is_primary_contact"
                  :label="t('guardians.isPrimaryContact')"
                />
                <VSwitch
                  v-model="linkForm.can_pickup"
                  :label="t('guardians.canPickup')"
                />
                <VSwitch
                  v-model="linkForm.receives_notifications"
                  :label="t('guardians.receivesNotifications')"
                />
              </VCol>

              <VCol cols="12" class="d-flex justify-end">
                <VBtn
                  :loading="linkForm.processing"
                  :disabled="!linkForm.student_id || !linkForm.relationship || isDuplicate"
                  @click="submitLink"
                >
                  {{ t('guardians.linkStudent') }}
                </VBtn>
              </VCol>
            </VRow>
          </form>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
