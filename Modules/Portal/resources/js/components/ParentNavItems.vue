<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PortalNavItems from './PortalNavItems.vue'

const { t } = useI18n()

const sections = computed(() => [
  {
    key: 'overview',
    heading: null,
    children: [
      { title: t('portal.nav.dashboard'), to: '/parent', icon: 'bx-home-smile' },
      { title: t('portal.nav.children'), to: '/parent/children', icon: 'bx-group', module: 'Student' },
    ],
  },
  {
    key: 'followup',
    heading: t('portal.nav.followUp'),
    children: [
      { title: t('portal.nav.attendance'), to: '/parent/attendance', icon: 'bx-check-square', module: 'Attendance' },
      { title: t('portal.nav.homework'), to: '/parent/homework', icon: 'bx-task', module: 'Assignment' },
      { title: t('portal.nav.grades'), to: '/parent/grades', icon: 'bx-medal', module: 'Examination' },
      { title: t('portal.nav.materials'), to: '/parent/materials', icon: 'bx-folder-open', module: 'StudyMaterial' },
      { title: t('portal.nav.meetings'), to: '/parent/meetings', icon: 'bx-video', module: 'Meeting' },
    ],
  },
  {
    key: 'finance',
    heading: t('portal.nav.finance'),
    children: [
      { title: t('portal.nav.fees'), to: '/parent/fees', icon: 'bx-receipt', module: 'Finance' },
      { title: t('portal.nav.payments'), to: '/parent/payments', icon: 'bx-credit-card', module: 'Finance' },
    ],
  },
  {
    key: 'account',
    heading: null,
    children: [
      { title: t('portal.nav.notifications'), to: '/notifications', icon: 'bx-bell', module: 'Notification' },
    ],
  },
])
</script>

<template>
  <PortalNavItems :sections="sections" />
</template>
