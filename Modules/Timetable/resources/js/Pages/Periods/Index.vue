<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppDataTable from '@/components/AppDataTable.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  periods: { type: Object, required: true },
  weekdays: { type: Array, required: true },
  activeWeekdays: { type: Array, default: () => [] },
})

const { t } = useI18n()

/** Period times are stored as HH:MM:SS; seconds are noise on screen. */
function shortTime(value) {
  return typeof value === 'string' ? value.slice(0, 5) : value
}

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const weekdayForm = useForm({
  active_weekdays: [...props.activeWeekdays],
})

function saveWeekdays() {
  weekdayForm.patch('/schedule/weekday-settings', { preserveScroll: true })
}

const headers = computed(() => [
  { title: t('timetables.periods.periodNumber'), key: 'period_number' },
  { title: t('timetables.periods.name'), key: 'name', sortable: false },
  { title: t('timetables.periods.time'), key: 'time', sortable: false },
  { title: t('timetables.periods.isBreak'), key: 'is_break', sortable: false },
  { title: t('timetables.periods.isActive'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('timetables.periods.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/schedule/periods/${item.id}`, { preserveScroll: true })
    },
  })
}
</script>

<template>
  <Head :title="t('timetables.periods.title')" />

  <VRow>
    <VCol cols="12">
  <AppPageHeader
    :title="t('timetables.periods.weekdaysTitle')"
    :subtitle="t('timetables.periods.weekdaysSubtitle')"
  />

  <VCard>

        <VDivider />

        <VCardText>
          <div class="d-flex flex-wrap gap-4">
            <VCheckbox
              v-for="day in weekdays"
              :key="day.value"
              v-model="weekdayForm.active_weekdays"
              :label="day.label"
              :value="day.value"
              hide-details
            />
          </div>
          <p v-if="weekdayForm.errors.active_weekdays" class="text-error text-caption mt-2 mb-0">
            {{ weekdayForm.errors.active_weekdays }}
          </p>
        </VCardText>

        <VDivider />

        <VCardText class="d-flex justify-end">
          <VBtn :loading="weekdayForm.processing" @click="saveWeekdays">
            {{ t('common.save') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <div class="d-flex flex-wrap align-center justify-space-between gap-4 mb-4">
        <div>
          <h2 class="text-h5 mb-0">
            {{ t('timetables.periods.title') }}
          </h2>
          <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
            {{ t('timetables.periods.subtitle') }}
          </p>
        </div>

        <VBtn
          prepend-icon="bx-plus"
          :href="'/schedule/periods/create'"
          :as="Link"
        >
          {{ t('timetables.periods.addNew') }}
        </VBtn>
      </div>

      <AppDataTable
        :headers="headers"
        :items="periods.data"
        mobile-title-key="name"
        empty-icon="bx-time-five"
        :empty-title="t('timetables.periods.empty')"
      >
          <template #item.name="{ item }">
            {{ item.name }}
          </template>

          <template #item.time="{ item }">
            {{ shortTime(item.start_time) }} - {{ shortTime(item.end_time) }}
          </template>

          <template #item.is_break="{ item }">
            <VChip :color="item.is_break ? 'warning' : 'secondary'" size="small">
              {{ item.is_break ? t('common.yes') : t('common.no') }}
            </VChip>
          </template>

          <template #item.is_active="{ item }">
            <VChip :color="item.is_active ? 'success' : 'secondary'" size="small">
              {{ item.is_active ? t('common.active') : t('common.no') }}
            </VChip>
          </template>

          <template #item.actions="{ item }">
            <IconBtn :href="`/schedule/periods/${item.id}/edit`" :as="Link">
              <VIcon icon="bx-edit" />
            </IconBtn>
            <IconBtn @click="archive(item)">
              <VIcon icon="bx-archive" />
            </IconBtn>
          </template>

      </AppDataTable>
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
