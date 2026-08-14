<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
import { isNavLinkActive } from '@layouts/utils'

const { t } = useI18n()
const page = usePage()

const DASHBOARD_LINK = { to: '/dashboard', icon: 'bx-home-smile' }

const enabledModules = computed(() => page.props.enabledModules ?? [])

const isModuleEnabled = name => !name || enabledModules.value.includes(name)

/*
 * Keeps entries the user cannot open out of the menu. Server-side policies
 * remain the actual guard — this is only to avoid showing a dead link.
 */
const abilities = computed(() => page.props.can ?? {})

const isPermitted = permission => !permission || abilities.value[permission] === true

const sections = computed(() => [
  {
    key: 'academic',
    heading: t('nav.academic'),
    children: [
      { title: t('nav.academicYears'), to: '/academic/academic-years', icon: 'bx-calendar' },
      { title: t('nav.semesters'), to: '/academic/semesters', icon: 'bx-calendar-event' },
      { title: t('nav.gradeLevels'), to: '/academic/grade-levels', icon: 'bx-layer' },
      { title: t('nav.classrooms'), to: '/academic/classrooms', icon: 'bx-building', module: 'Classroom' },
      { title: t('nav.classes'), to: '/academic/classes', icon: 'bx-chalkboard', module: 'SchoolClass' },
      { title: t('nav.subjects'), to: '/academic/subjects', icon: 'bx-book-open', module: 'Subject' },
    ],
  },
  {
    key: 'user_management',
    heading: t('nav.user_management'),
    children: [
      { title: t('nav.students'), to: '/user-management/students', icon: 'bx-user', module: 'Student' },
      { title: t('nav.teachers'), to: '/user-management/teachers', icon: 'bx-briefcase-alt-2', module: 'Teacher' },
      { title: t('nav.guardians'), to: '/user-management/guardians', icon: 'bx-group', module: 'GuardianProfile' },
      { title: t('nav.promotions'), to: '/user-management/promotions', icon: 'bx-up-arrow-alt', module: 'Promotion', permission: 'viewPromotions' },
    ],
  },
  {
    key: 'access_control',
    heading: t('nav.accessControl'),
    children: [
      { title: t('nav.admins'), to: '/access-control/admins', icon: 'bx-user-circle', permission: 'viewUsers' },
      { title: t('nav.roles'), to: '/access-control/roles', icon: 'bx-shield-quarter', permission: 'viewRoles' },
    ],
  },
  {
    key: 'schedule',
    heading: t('nav.schedule'),
    children: [
      { title: t('nav.periods'), to: '/schedule/periods', icon: 'bx-time-five', module: 'Timetable' },
      { title: t('nav.timetables'), to: '/schedule/timetables', icon: 'bx-calendar-week', module: 'Timetable' },
    ],
  },
  {
    key: 'assignments',
    heading: t('nav.assignments'),
    children: [
      { title: t('nav.assignments'), to: '/assignments/assignments', icon: 'bx-task', module: 'Assignment' },
    ],
  },
  {
    key: 'meetings',
    heading: t('nav.meetings'),
    children: [
      { title: t('nav.meetings'), to: '/meetings/meetings', icon: 'bx-video', module: 'Meeting' },
    ],
  },
  {
    key: 'communication',
    heading: t('nav.communication'),
    children: [
      { title: t('nav.chat'), to: '/communication/chat', icon: 'bx-conversation', module: 'Communication' },
      { title: t('nav.messages'), to: '/communication/messages', icon: 'bx-message-detail', module: 'Communication' },
      { title: t('nav.emailBroadcast'), to: '/communication/email-broadcast', icon: 'bx-envelope', module: 'Communication' },
      { title: t('nav.news'), to: '/communication/news', icon: 'bx-news', module: 'News', permission: 'viewNews' },
    ],
  },
  {
    key: 'attendance',
    heading: t('nav.attendance'),
    children: [
      { title: t('nav.attendanceDashboard'), to: '/attendance/dashboard', icon: 'bx-tachometer', module: 'Attendance' },
      { title: t('nav.attendanceTake'), to: '/attendance/take', icon: 'bx-list-check', module: 'Attendance' },
      { title: t('nav.attendanceRecords'), to: '/attendance/records', icon: 'bx-calendar-check', module: 'Attendance' },
      { title: t('nav.attendanceReport'), to: '/attendance/report', icon: 'bx-bar-chart-alt-2', module: 'Attendance' },
    ],
  },
  {
    key: 'behavior',
    heading: t('nav.behavior'),
    children: [
      { title: t('nav.behaviorRecords'), to: '/behavior/records', icon: 'bx-shield-quarter', module: 'Behavior' },
      { title: t('nav.behaviorCategories'), to: '/behavior/categories', icon: 'bx-list-check', module: 'Behavior' },
    ],
  },
  {
    key: 'examination',
    heading: t('nav.examination'),
    children: [
      { title: t('nav.exams'), to: '/examination/exams', icon: 'bx-file', module: 'Examination' },
      { title: t('nav.examGrades'), to: '/examination/grades', icon: 'bx-medal', module: 'Examination' },
      { title: t('nav.reportCards'), to: '/examination/report-cards', icon: 'bx-id-card', module: 'Examination' },
    ],
  },
  {
    key: 'finance',
    heading: t('nav.finance'),
    children: [
      { title: t('nav.financeDashboard'), to: '/finance/dashboard', icon: 'bx-tachometer', module: 'Finance' },
      { title: t('nav.invoices'), to: '/finance/invoices', icon: 'bx-receipt', module: 'Finance' },
      { title: t('nav.payments'), to: '/finance/payments', icon: 'bx-credit-card', module: 'Finance' },
      { title: t('nav.studentFees'), to: '/finance/student-fees', icon: 'bx-user-check', module: 'Finance' },
      { title: t('nav.feeStructures'), to: '/finance/fee-structures', icon: 'bx-layer', module: 'Finance' },
      { title: t('nav.feeCategories'), to: '/finance/fee-categories', icon: 'bx-purchase-tag', module: 'Finance' },
      { title: t('nav.feeDiscounts'), to: '/finance/fee-discounts', icon: 'bx-gift', module: 'Finance' },
    ],
  },

  {
    key: 'reports',
    heading: t('nav.reports'),
    children: [
      { title: t('nav.reports'), to: '/reports', icon: 'bx-bar-chart-alt-2', module: 'Report' },
      { title: t('nav.reportTemplates'), to: '/reports/templates', icon: 'bx-file', module: 'Report' },
    ],
  },
  {
    key: 'settings',
    heading: t('nav.settings'),
    children: [
      { title: t('nav.languages'), to: '/settings/languages', icon: 'bx-globe' },
      { title: t('nav.aiTranslationSettings'), to: '/settings/ai-translation', icon: 'bx-globe' },
      { title: t('nav.schoolTerms'), to: '/settings/school-terms', icon: 'bx-file', permission: 'viewSchoolTerms' },
      { title: t('nav.systemErrors'), to: '/system/errors', icon: 'bx-bug', permission: 'viewErrorReports' },
      { title: t('nav.systemActivity'), to: '/system/activity', icon: 'bx-history', permission: 'viewActivity' },
    ],
  },
  {
    key: 'account',
    heading: t('nav.account'),
    children: [
      { title: t('nav.accountSettings'), to: '/account-settings', icon: 'bx-user' },
    ],
  },
])

const visibleSections = computed(() => sections.value
  .map(section => ({
    ...section,
    children: section.children.filter(child => isModuleEnabled(child.module) && isPermitted(child.permission)),
  }))
  .filter(section => section.children.length > 0))

/*
 * Some destinations are prefixes of others — `/reports` and
 * `/reports/templates`, for example — so a plain prefix test would light up
 * both entries on the child page. Resolving the single longest match here,
 * where every destination is visible at once, keeps exactly one item active
 * without any per-route special casing.
 */
const activePath = computed(() => {
  const candidates = [
    DASHBOARD_LINK.to,
    ...visibleSections.value.flatMap(section => section.children.map(child => child.to)),
  ]

  return candidates
    .filter(to => isNavLinkActive(to, page.url))
    .sort((a, b) => b.length - a.length)[0] ?? null
})
</script>

<template>
  <VerticalNavLink
    :item="{
      title: t('nav.dashboards'),
      to: DASHBOARD_LINK.to,
      icon: DASHBOARD_LINK.icon,
    }"
    :is-active="activePath === DASHBOARD_LINK.to"
  />

  <template
    v-for="section in visibleSections"
    :key="section.key"
  >
    <VerticalNavSectionTitle :item="{ heading: section.heading }" />
    <VerticalNavLink
      v-for="child in section.children"
      :key="child.to"
      :item="{ title: child.title, to: child.to, icon: child.icon }"
      :is-active="activePath === child.to"
    />
  </template>
</template>
