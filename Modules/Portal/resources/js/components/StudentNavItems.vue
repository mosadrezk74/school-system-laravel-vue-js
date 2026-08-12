<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PortalNavItems from './PortalNavItems.vue'

const { t } = useI18n()

const sections = computed(() => [
  {
    key: 'today',
    heading: null,
    children: [
      { title: t('portal.nav.dashboard'), to: '/student', icon: 'bx-home-smile' },
      { title: t('portal.nav.schedule'), to: '/student/schedule', icon: 'bx-calendar-week', module: 'Timetable' },
    ],
  },
  {
    key: 'learning',
    heading: t('portal.nav.learning'),
    children: [
      { title: t('portal.nav.homework'), to: '/student/homework', icon: 'bx-task', module: 'Assignment' },
      { title: t('portal.nav.grades'), to: '/student/grades', icon: 'bx-medal', module: 'Examination' },
      { title: t('portal.nav.reportCard'), to: '/student/report-card', icon: 'bx-id-card', module: 'Examination' },
      { title: t('portal.nav.attendance'), to: '/student/attendance', icon: 'bx-check-square', module: 'Attendance' },
    ],
  },
  {
    key: 'live',
    heading: t('portal.nav.live'),
    children: [
      { title: t('portal.nav.meetings'), to: '/student/meetings', icon: 'bx-video', module: 'Meeting' },
    ],
  },
  {
    key: 'account',
    heading: null,
    children: [
      { title: t('portal.nav.notifications'), to: '/notifications', icon: 'bx-bell', module: 'Notification' },
      { title: t('portal.nav.news'), to: '/news', icon: 'bx-news', module: 'News' },
      { title: t('portal.nav.terms'), to: '/terms', icon: 'bx-file' },
    ],
  },
])
</script>

<template>
  <PortalNavItems :sections="sections" />
</template>
