<script setup>
/**
 * Admin dashboard.
 *
 * Every figure rendered here arrives from the server already calculated and
 * already scoped to the current academic year — this page performs no
 * business arithmetic of its own, it only formats.
 *
 * A section arrives as `null` when the signed-in user may not see it, which is
 * rendered as absence. That is deliberately different from a section that is
 * present but empty ("no attendance taken yet"), which gets an empty state.
 */
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppStatCard from '@/components/AppStatCard.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import { useMoney } from '@modules/Finance/resources/js/composable/useMoney'

const props = defineProps({
  overview: { type: Object, required: true },
  academicYear: { type: Object, default: null },
})

const { t, locale } = useI18n()
const page = usePage()
const { formatMoney } = useMoney()

const user = computed(() => page.props.auth?.user ?? null)

/**
 * The date is built from the active locale rather than a stored string, so
 * Arabic renders Arabic month names and English renders English ones.
 */
const today = computed(() => new Intl.DateTimeFormat(locale.value, {
  weekday: 'long',
  day: 'numeric',
  month: 'long',
  year: 'numeric',
}).format(new Date()))

const people = computed(() => props.overview.people ?? {})
const attendance = computed(() => props.overview.attendance ?? null)
const finance = computed(() => props.overview.finance ?? null)
const attention = computed(() => props.overview.attention ?? [])

const numberFormatter = computed(() => new Intl.NumberFormat(locale.value))

const formatNumber = value =>
  (value === null || value === undefined ? null : numberFormatter.value.format(value))

/*
 * Only tiles whose section the user may see are built. `value: null` reaches
 * AppStatCard, which renders an em dash for "not measured" — it never
 * substitutes a zero.
 */
const tiles = computed(() => {
  const cards = []

  if (people.value.students !== null && people.value.students !== undefined) {
    cards.push({
      key: 'students',
      icon: 'bx-user-voice',
      value: formatNumber(people.value.students),
      hint: people.value.newStudents
        ? t('dashboard.newStudentsHint', { count: people.value.newStudents })
        : t('dashboard.studentsHint'),
    })
  }

  if (people.value.teachers !== null && people.value.teachers !== undefined) {
    cards.push({ key: 'teachers', icon: 'bx-chalkboard', value: formatNumber(people.value.teachers) })
  }

  if (people.value.classes !== null && people.value.classes !== undefined) {
    cards.push({ key: 'classes', icon: 'bx-group', value: formatNumber(people.value.classes) })
  }

  if (people.value.guardians !== null && people.value.guardians !== undefined) {
    cards.push({
      key: 'guardians',
      icon: 'bx-user-check',
      value: formatNumber(people.value.guardians),
      hint: t('dashboard.guardiansHint'),
    })
  }

  if (people.value.classrooms !== null && people.value.classrooms !== undefined) {
    cards.push({
      key: 'classrooms',
      icon: 'bx-door-open',
      value: formatNumber(people.value.classrooms),
      hint: t('dashboard.classroomsHint'),
    })
  }

  if (finance.value) {
    cards.push({
      key: 'outstandingFees',
      icon: 'bx-receipt',
      /*
       * Nothing billed yet is not the same as nothing owed, so the tile shows
       * "not measured" rather than a reassuring zero balance.
       */
      value: finance.value.hasInvoices ? formatMoney(finance.value.outstanding.amount) : null,
      hint: finance.value.hasInvoices
        ? t('dashboard.acrossInvoices', { count: finance.value.outstanding.invoices })
        : t('dashboard.noInvoicesYet'),
    })
  }

  return cards
})

const attendanceToday = computed(() => attendance.value?.today ?? null)

const genderBreakdown = computed(() => {
  const source = people.value.studentsByGender

  if (!source) return []

  return Object.entries(source).map(([gender, count]) => ({
    gender,
    count,
    label: t(`dashboard.gender.${gender}`, gender),
  }))
})

const classCapacity = computed(() => people.value.classCapacity ?? [])

const hasFinanceActivity = computed(() => Boolean(finance.value?.hasInvoices))

/*
 * Quick actions are authorised server-side; the page only renders what the
 * controller already decided this user may do.
 */
const quickActionIcons = {
  addStudent: 'bx-user-plus',
  addTeacher: 'bx-chalkboard',
  createClass: 'bx-group',
  createInvoice: 'bx-receipt',
}

const quickActions = computed(() => props.overview.quickActions ?? [])
</script>

<template>
  <Head :title="t('dashboard.title')" />

  <AppPageHeader
    :title="user?.name
      ? t('dashboard.welcomeUser', { name: user.name })
      : t('dashboard.greeting')"
    :subtitle="today"
  >
    <template #actions>
      <VChip
        v-if="academicYear"
        color="primary"
        variant="tonal"
        prepend-icon="bx-calendar"
      >
        {{ t('dashboard.currentYear') }}: {{ academicYear.name }}
      </VChip>

      <VChip
        v-else
        color="warning"
        variant="tonal"
        prepend-icon="bx-error"
      >
        {{ t('dashboard.noCurrentYear') }}
      </VChip>
    </template>
  </AppPageHeader>

  <!-- KPI row -->
  <div
    v-if="tiles.length"
    class="dashboard-metrics"
  >
    <AppStatCard
      v-for="tile in tiles"
      :key="tile.key"
      :title="t(`dashboard.${tile.key}`)"
      :value="tile.value"
      :icon="tile.icon"
      :hint="tile.hint"
    />
  </div>

  <VRow class="mt-1">
    <!-- Attendance -->
    <VCol
      v-if="attendance"
      cols="12"
      md="6"
    >
      <VCard height="100%">
        <VCardItem>
          <VCardTitle>{{ t('dashboard.attendanceToday') }}</VCardTitle>
        </VCardItem>

        <!--
          `taken` is false when no class has recorded attendance yet. Showing
          0% present would wrongly imply every student was absent.
        -->
        <AppEmptyState
          v-if="!attendanceToday?.taken"
          icon="bx-check-square"
          :title="t('dashboard.attendanceNotTaken')"
          :description="t('dashboard.attendanceNotTakenHint')"
        />

        <VCardText v-else>
          <div class="d-flex align-baseline gap-2">
            <span class="text-h3 app-numeric">{{ attendanceToday.rate }}%</span>
            <span class="text-body-2 text-medium-emphasis">
              {{ t('dashboard.presentOf', {
                present: attendanceToday.present + attendanceToday.late,
                total: attendanceToday.total,
              }) }}
            </span>
          </div>

          <VProgressLinear
            :model-value="attendanceToday.rate"
            color="success"
            height="8"
            rounded
            class="mt-4"
          />

          <div class="d-flex flex-wrap gap-4 mt-4">
            <div
              v-for="stat in ['present', 'absent', 'late', 'excused']"
              :key="stat"
            >
              <div class="text-body-2 text-medium-emphasis">
                {{ t(`dashboard.attendance.${stat}`) }}
              </div>
              <div class="text-h6 app-numeric">
                {{ formatNumber(attendanceToday[stat]) }}
              </div>
            </div>
          </div>

          <p class="text-caption text-medium-emphasis mb-0 mt-4">
            {{ t('dashboard.attendanceClassesTaken', { count: attendanceToday.classesTaken }) }}
          </p>
        </VCardText>
      </VCard>
    </VCol>

    <!-- Finance -->
    <VCol
      v-if="finance"
      cols="12"
      md="6"
    >
      <VCard height="100%">
        <VCardItem>
          <VCardTitle>{{ t('dashboard.financeOverview') }}</VCardTitle>
        </VCardItem>

        <AppEmptyState
          v-if="!hasFinanceActivity"
          icon="bx-receipt"
          :title="t('dashboard.noInvoicesYet')"
          :description="t('dashboard.noInvoicesYetHint')"
        />

        <VCardText v-else>
          <VRow>
            <VCol
              cols="12"
              sm="6"
            >
              <div class="text-body-2 text-medium-emphasis">
                {{ t('dashboard.outstandingFees') }}
              </div>
              <div class="text-h6 app-numeric">
                {{ formatMoney(finance.outstanding.amount) }}
              </div>
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <div class="text-body-2 text-medium-emphasis">
                {{ t('dashboard.overdueAmount') }}
              </div>
              <div
                class="text-h6 app-numeric"
                :class="finance.overdue.amount > 0 ? 'text-error' : ''"
              >
                {{ formatMoney(finance.overdue.amount) }}
              </div>
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <div class="text-body-2 text-medium-emphasis">
                {{ t('dashboard.collectedThisMonth') }}
              </div>
              <div class="text-h6 app-numeric">
                {{ formatMoney(finance.collectedThisMonth) }}
              </div>
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <div class="text-body-2 text-medium-emphasis">
                {{ t('dashboard.pendingPayments') }}
              </div>
              <div class="text-h6 app-numeric">
                {{ formatNumber(finance.awaitingConfirmation.count) }}
              </div>
            </VCol>
          </VRow>

          <p class="text-caption text-medium-emphasis mb-0 mt-2">
            {{ t('dashboard.pendingNotCounted') }}
          </p>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <VRow>
    <!-- Needs attention -->
    <VCol
      cols="12"
      md="6"
    >
      <VCard height="100%">
        <VCardItem>
          <VCardTitle>{{ t('dashboard.needsAttention') }}</VCardTitle>
        </VCardItem>

        <AppEmptyState
          v-if="!attention.length"
          icon="bx-check-circle"
          :title="t('dashboard.nothingNeedsAttention')"
        />

        <VList
          v-else
          lines="two"
        >
          <VListItem
            v-for="item in attention"
            :key="item.key"
            :href="item.url ?? undefined"
            :as="item.url ? Link : 'div'"
          >
            <template #prepend>
              <VAvatar
                :color="item.severity"
                variant="tonal"
                rounded="lg"
              >
                <VIcon icon="bx-error-circle" />
              </VAvatar>
            </template>

            <VListItemTitle>{{ t(`dashboard.attention.${item.key}`) }}</VListItemTitle>
            <VListItemSubtitle>
              {{ t(`dashboard.attentionCount.${item.key}`, { count: item.count }) }}
            </VListItemSubtitle>

            <template #append>
              <VIcon
                icon="bx-chevron-right"
                class="flip-in-rtl"
              />
            </template>
          </VListItem>
        </VList>
      </VCard>
    </VCol>

    <!-- Class capacity -->
    <VCol
      v-if="classCapacity.length"
      cols="12"
      md="6"
    >
      <VCard height="100%">
        <VCardItem>
          <VCardTitle>{{ t('dashboard.classCapacity') }}</VCardTitle>
        </VCardItem>

        <VCardText>
          <div
            v-for="entry in classCapacity"
            :key="entry.id"
            class="mb-4"
          >
            <div class="d-flex align-center justify-space-between gap-2">
              <span class="text-body-2 text-truncate">{{ entry.name }}</span>
              <span class="text-body-2 app-numeric flex-shrink-0">
                {{ formatNumber(entry.students) }}<template v-if="entry.capacity">
                  / {{ formatNumber(entry.capacity) }}
                </template>
              </span>
            </div>

            <VProgressLinear
              v-if="entry.capacity"
              :model-value="(entry.students / entry.capacity) * 100"
              :color="entry.students > entry.capacity ? 'error' : 'primary'"
              height="6"
              rounded
              class="mt-1"
            />
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <VRow>
    <!-- Students by gender -->
    <VCol
      v-if="genderBreakdown.length"
      cols="12"
      md="6"
    >
      <VCard height="100%">
        <VCardItem>
          <VCardTitle>{{ t('dashboard.studentsByGender') }}</VCardTitle>
        </VCardItem>

        <VCardText>
          <div
            v-for="entry in genderBreakdown"
            :key="entry.gender"
            class="d-flex align-center justify-space-between py-2"
          >
            <span class="text-body-2">{{ entry.label }}</span>
            <span class="text-h6 app-numeric">{{ formatNumber(entry.count) }}</span>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <!-- Quick actions -->
    <VCol
      v-if="quickActions.length"
      cols="12"
      md="6"
    >
      <VCard height="100%">
        <VCardItem>
          <VCardTitle>{{ t('dashboard.quickActions') }}</VCardTitle>
        </VCardItem>

        <VCardText class="d-flex flex-wrap gap-3">
          <VBtn
            v-for="action in quickActions"
            :key="action.key"
            :href="action.url"
            :as="Link"
            variant="tonal"
            color="primary"
            :prepend-icon="quickActionIcons[action.key]"
          >
            {{ t(`dashboard.actions.${action.key}`) }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss" scoped>
.dashboard-metrics {
  display: grid;
  gap: var(--app-space-4);

  /* One column on a phone, then as many ~15rem columns as fit. */
  grid-template-columns: 1fr;

  @media (min-width: 600px) {
    grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
  }
}
</style>
