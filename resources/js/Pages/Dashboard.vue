<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  stats: { type: Object, required: true },
  currentYear: { type: Object, default: null },
})

const { t } = useI18n()

/*
 * A null stat means the owning module hasn't shipped yet. An em dash says
 * "not measured"; a zero would claim the school has no students.
 */
const format = value => (value === null || value === undefined ? '—' : value)

const tiles = computed(() => [
  { key: 'students', icon: 'bx-user-voice', value: props.stats.students, hint: t('dashboard.studentsHint') },
  { key: 'teachers', icon: 'bx-chalkboard', value: props.stats.teachers, hint: null },
  { key: 'attendanceToday', icon: 'bx-check-square', value: props.stats.attendance, hint: null },
  { key: 'outstandingFees', icon: 'bx-receipt', value: props.stats.outstandingFees, hint: null },
])
</script>

<template>
  <Head :title="t('dashboard.title')" />

  <VRow>
    <VCol cols="12">
      <div class="d-flex flex-wrap align-center justify-space-between gap-4 mb-2">
        <div>
          <h4 class="text-h4 mb-1">
            {{ t('dashboard.greeting') }}
          </h4>
          <p class="text-body-1 mb-0 text-medium-emphasis">
            <template v-if="currentYear">
              {{ t('dashboard.currentYear') }}: <strong>{{ currentYear.name }}</strong>
            </template>
            <template v-else>
              {{ t('dashboard.noCurrentYear') }}
            </template>
          </p>
        </div>

        <Link href="/academic/academic-years">
          <VBtn
            variant="tonal"
            prepend-icon="bx-calendar"
          >
            {{ t('nav.academicYears') }}
          </VBtn>
        </Link>
      </div>
    </VCol>

    <VCol
      v-for="tile in tiles"
      :key="tile.key"
      cols="12"
      sm="6"
      lg="3"
    >
      <VCard>
        <VCardText>
          <div class="d-flex align-center gap-3 mb-3">
            <VAvatar
              rounded
              size="40"
              color="primary"
              variant="tonal"
            >
              <VIcon
                :icon="tile.icon"
                size="22"
              />
            </VAvatar>
            <span class="text-body-1 text-medium-emphasis">
              {{ t(`dashboard.${tile.key}`) }}
            </span>
          </div>

          <h4 class="text-h4">
            {{ format(tile.value) }}
          </h4>

          <p
            v-if="tile.hint"
            class="text-body-2 text-medium-emphasis mb-0 mt-1"
          >
            {{ tile.hint }}
          </p>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ t('dashboard.needsAttention') }}</VCardTitle>
        </VCardItem>
        <VCardText class="text-medium-emphasis">
          {{ t('dashboard.noData') }}
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
