<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import PortalStatCard from '@modules/Portal/resources/js/components/PortalStatCard.vue'
import ChildSelector from '@modules/Portal/resources/js/components/ChildSelector.vue'

defineOptions({ layout: ParentLayout })

const props = defineProps({
  history: { type: Object, default: null },
  children: { type: Array, default: () => [] },
  selectedChild: { type: [Number, null], default: null },
})

const { t } = useI18n()

const summary = computed(() => props.history?.summary ?? {})

const tiles = computed(() => [
  { key: 'present', field: 'present_count', icon: 'bx-check-circle', color: 'success' },
  { key: 'absent', field: 'absent_count', icon: 'bx-x-circle', color: 'error' },
  { key: 'late', field: 'late_count', icon: 'bx-time-five', color: 'warning' },
  { key: 'rate', field: 'attendance_percentage', icon: 'bx-line-chart', color: 'primary' },
].map(tile => ({
  ...tile,
  title: t(`portal.parent.attendance.${tile.key}`),
  value: tile.key === 'rate'
    ? `${summary.value[tile.field] ?? 0}%`
    : (summary.value[tile.field] ?? 0),
})))

const timeline = computed(() => props.history?.timeline ?? [])

const statusColors = {
  present: 'success',
  absent: 'error',
  late: 'warning',
  excused: 'info',
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString() : '—'
}
</script>

<template>
  <Head :title="t('portal.nav.attendance')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.attendance')"
        :subtitle="t('portal.parent.attendanceSubtitle')"
        icon="bx-check-square"
      >
        <template #actions>
          <ChildSelector
            :children="children"
            :selected="selectedChild"
            url="/parent/attendance"
          />
        </template>
      </PortalHeader>
    </VCol>

    <VCol
      v-for="tile in tiles"
      :key="tile.key"
      cols="12"
      sm="6"
      lg="3"
    >
      <PortalStatCard
        :title="tile.title"
        :value="tile.value"
        :icon="tile.icon"
        :color="tile.color"
      />
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('portal.parent.history') }}</VCardTitle>
        </VCardItem>

        <VDivider />

        <VList v-if="timeline.length" density="compact" max-height="480" class="overflow-y-auto">
          <VListItem
            v-for="(row, index) in timeline"
            :key="index"
          >
            <VListItemTitle>{{ formatDate(row.date) }}</VListItemTitle>
            <VListItemSubtitle v-if="row.notes">
              {{ row.notes }}
            </VListItemSubtitle>

            <template #append>
              <VChip
                :color="statusColors[row.status] ?? 'secondary'"
                size="small"
                variant="tonal"
              >
                {{ t(`portal.parent.attendance.${row.status}`) }}
              </VChip>
            </template>
          </VListItem>
        </VList>

        <VCardText v-else class="d-flex flex-column align-center pa-8 text-center text-disabled">
          <VIcon icon="bx-calendar-x" size="40" class="mb-2" />
          {{ t('portal.parent.noAttendance') }}
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
