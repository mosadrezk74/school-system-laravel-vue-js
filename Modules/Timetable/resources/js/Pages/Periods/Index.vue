<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  periods: { type: Object, required: true },
  weekdays: { type: Array, required: true },
  activeWeekdays: { type: Array, default: () => [] },
})

const { t } = useI18n()

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
  if (!confirm(t('timetables.periods.confirmArchive')))
    return

  router.delete(`/schedule/periods/${item.id}`, { preserveScroll: true })
}
</script>

<template>
  <Head :title="t('timetables.periods.title')" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('timetables.periods.weekdaysTitle') }}</VCardTitle>
          <VCardSubtitle>{{ t('timetables.periods.weekdaysSubtitle') }}</VCardSubtitle>
        </VCardItem>

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
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('timetables.periods.title') }}</VCardTitle>
          <VCardSubtitle>{{ t('timetables.periods.subtitle') }}</VCardSubtitle>

          <template #append>
            <VBtn
              prepend-icon="bx-plus"
              :href="'/schedule/periods/create'"
              :as="Link"
            >
              {{ t('timetables.periods.addNew') }}
            </VBtn>
          </template>
        </VCardItem>

        <VDivider />

        <VDataTable
          :headers="headers"
          :items="periods.data"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.name="{ item }">
            {{ item.name }}
          </template>

          <template #item.time="{ item }">
            {{ item.start_time }} - {{ item.end_time }}
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

          <template #no-data>
            <div class="d-flex flex-column align-center pa-8 text-center text-disabled">
              <VIcon icon="bx-time-five" size="40" class="mb-2" />
              {{ t('timetables.periods.empty') }}
            </div>
          </template>
        </VDataTable>
      </VCard>
    </VCol>
  </VRow>
</template>
