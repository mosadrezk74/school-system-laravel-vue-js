<script setup>
import { Link, router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  record: { type: Object, required: true },
})

const { t } = useI18n()

const statusColors = {
  present: 'success',
  absent: 'error',
  late: 'warning',
  excused: 'info',
}

function destroy() {
  if (!confirm(t('attendanceRecords.confirmDelete')))
    return

  router.delete(`/attendance/records/${props.record.id}`)
}
</script>

<template>
  <Head :title="t('attendanceRecords.title')" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar color="primary" variant="tonal" size="56">
            <VIcon icon="bx-calendar-check" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ record.date }}
              </h5>
              <VChip color="primary" size="small" variant="tonal">
                {{ t('attendanceRecords.sessionLabel', { session: record.session }) }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ record.class ? record.class.name : '—' }}
              <template v-if="record.subject">
                · {{ record.subject.name }}
              </template>
            </p>
          </div>

          <VBtn
            prepend-icon="bx-edit"
            :href="`/attendance/records/${record.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
          <VBtn
            color="error"
            variant="tonal"
            prepend-icon="bx-trash"
            @click="destroy"
          >
            {{ t('common.delete') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VRow>
        <VCol cols="6" md="3">
          <VCard color="success" variant="tonal">
            <VCardText class="text-center">
              <div class="text-h4">
                {{ record.present_count }}
              </div>
              <div class="text-body-2">
                {{ t('attendanceRecords.present') }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="6" md="3">
          <VCard color="error" variant="tonal">
            <VCardText class="text-center">
              <div class="text-h4">
                {{ record.absent_count }}
              </div>
              <div class="text-body-2">
                {{ t('attendanceRecords.absent') }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="6" md="3">
          <VCard color="warning" variant="tonal">
            <VCardText class="text-center">
              <div class="text-h4">
                {{ record.late_count }}
              </div>
              <div class="text-body-2">
                {{ t('attendanceRecords.late') }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="6" md="3">
          <VCard color="info" variant="tonal">
            <VCardText class="text-center">
              <div class="text-h4">
                {{ record.excused_count }}
              </div>
              <div class="text-body-2">
                {{ t('attendanceRecords.excused') }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-list-ul" />
          </template>
          <VCardTitle>{{ t('attendanceRecords.roster') }}</VCardTitle>
        </VCardItem>

        <VCardText v-if="!record.details || record.details.length === 0" class="text-disabled">
          {{ t('attendanceRecords.noStudentsInClass') }}
        </VCardText>

        <VTable v-else>
          <thead>
            <tr>
              <th>{{ t('attendanceRecords.student') }}</th>
              <th>{{ t('attendanceRecords.admissionNumber') }}</th>
              <th>{{ t('attendanceRecords.status') }}</th>
              <th>{{ t('attendanceRecords.checkInTime') }}</th>
              <th>{{ t('attendanceRecords.checkOutTime') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="detail in record.details" :key="detail.id">
              <td>{{ detail.student_name ?? '—' }}</td>
              <td>{{ detail.admission_number ?? '—' }}</td>
              <td>
                <VChip :color="statusColors[detail.status] ?? 'secondary'" size="small">
                  {{ t(`attendanceRecords.statuses.${detail.status}`) }}
                </VChip>
              </td>
              <td>{{ detail.check_in_time ?? '—' }}</td>
              <td>{{ detail.check_out_time ?? '—' }}</td>
            </tr>
          </tbody>
        </VTable>
      </VCard>
    </VCol>
  </VRow>
</template>
