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
      { title: t('portal.nav.dashboard'), to: '/teacher', icon: 'bx-home-smile' },
      { title: t('portal.nav.schedule'), to: '/teacher/schedule', icon: 'bx-calendar-week' },
    ],
  },
  {
    key: 'teaching',
    heading: t('portal.nav.teaching'),
    children: [
      { title: t('portal.nav.myClasses'), to: '/teacher/classes', icon: 'bx-chalkboard' },
      { title: t('portal.nav.attendance'), to: '/teacher/attendance', icon: 'bx-list-check', module: 'Attendance' },
      { title: t('portal.nav.homework'), to: '/teacher/homework', icon: 'bx-task', module: 'Assignment' },
      { title: t('portal.nav.studyMaterials'), to: '/teacher/study-materials', icon: 'bx-folder-open', module: 'StudyMaterial' },
      { title: t('portal.nav.meetings'), to: '/teacher/meetings', icon: 'bx-video', module: 'Meeting' },
    ],
  },
])
</script>

<template>
  <PortalNavItems :sections="sections" />
</template>
