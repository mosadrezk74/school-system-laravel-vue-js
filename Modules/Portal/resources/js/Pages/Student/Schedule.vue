<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import StudentLayout from '@modules/Portal/resources/js/layouts/student.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'

defineOptions({ layout: StudentLayout })

const props = defineProps({
  entries: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  weekdays: { type: Array, default: () => [] },
  today: { type: Number, default: 0 },
})

const { t } = useI18n()

const day = ref(props.filters.day ?? props.today)

watch(day, value => {
  router.get('/student/schedule', value === null ? {} : { day: value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
})

const dayOptions = computed(() => props.weekdays.map(item => ({
  title: item.label,
  value: item.value,
})))

const grouped = computed(() => {
  const map = new Map()

  props.entries.forEach(entry => {
    if (!map.has(entry.day_of_week))
      map.set(entry.day_of_week, [])

    map.get(entry.day_of_week).push(entry)
  })

  return Array.from(map, ([dayOfWeek, items]) => ({
    dayOfWeek,
    label: props.weekdays.find(w => w.value === dayOfWeek)?.label ?? '—',
    items,
  }))
})

function timeRange(entry) {
  if (!entry.start_time)
    return '—'

  return entry.end_time ? `${entry.start_time} – ${entry.end_time}` : entry.start_time
}
</script>

<template>
  <Head :title="t('portal.nav.schedule')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.schedule')"
        :subtitle="t('portal.student.scheduleSubtitle')"
        icon="bx-calendar-week"
      >
        <template #actions>
          <VSelect
            v-model="day"
            :items="dayOptions"
            :label="t('portal.student.day')"
            density="compact"
            clearable
            hide-details
            style="min-inline-size: 200px;"
          />
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText v-if="!grouped.length" class="d-flex flex-column align-center pa-12 text-center text-disabled">
          <VIcon icon="bx-calendar-x" size="48" class="mb-3" />
          {{ t('portal.student.noSchedule') }}
        </VCardText>

        <template v-else>
          <template
            v-for="group in grouped"
            :key="group.dayOfWeek"
          >
            <VCardItem>
              <VCardTitle class="text-body-1">
                {{ group.label }}
              </VCardTitle>
            </VCardItem>

            <VList density="compact">
              <VListItem
                v-for="entry in group.items"
                :key="entry.id"
              >
                <template #prepend>
                  <VAvatar
                    :color="entry.is_break ? 'secondary' : 'primary'"
                    variant="tonal"
                    size="40"
                  >
                    <VIcon :icon="entry.is_break ? 'bx-coffee' : 'bx-book-open'" />
                  </VAvatar>
                </template>

                <VListItemTitle>
                  {{ entry.subject ?? t('portal.student.break') }}
                </VListItemTitle>
                <VListItemSubtitle>
                  {{ timeRange(entry) }}
                  <span v-if="entry.teacher"> · {{ entry.teacher }}</span>
                  <span v-if="entry.classroom"> · {{ entry.classroom }}</span>
                </VListItemSubtitle>

                <template #append>
                  <VChip v-if="entry.is_online" size="small" color="info" variant="tonal">
                    {{ t('portal.student.online') }}
                  </VChip>
                </template>
              </VListItem>
            </VList>

            <VDivider />
          </template>
        </template>
      </VCard>
    </VCol>
  </VRow>
</template>
