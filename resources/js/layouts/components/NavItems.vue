<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'

const { t } = useI18n()
const page = usePage()

const enabledModules = computed(() => page.props.enabledModules ?? [])
const isModuleEnabled = name => enabledModules.value.includes(name)
</script>

<template>
  <VerticalNavLink
    :item="{
      title: t('nav.dashboards'),
      to: '/dashboard',
      icon: 'bx-home-smile',
    }"
  />

  <!-- 👉 Academic -->
  <VerticalNavSectionTitle :item="{ heading: t('nav.academic') }" />
  <VerticalNavLink
    :item="{
      title: t('nav.academicYears'),
      to: '/academic/academic-years',
      icon: 'bx-calendar',
    }"
  />
  <VerticalNavLink
    v-if="isModuleEnabled('SchoolClass')"
    :item="{
      title: t('nav.classes'),
      to: '/academic/classes',
      icon: 'bx-chalkboard',
    }"
  />
  <VerticalNavLink
    v-if="isModuleEnabled('Subject')"
    :item="{
      title: t('nav.subjects'),
      to: '/academic/subjects',
      icon: 'bx-book-open',
    }"
  />
  <VerticalNavLink
    v-if="isModuleEnabled('Classroom')"
    :item="{
      title: t('nav.classrooms'),
      to: '/academic/classrooms',
      icon: 'bx-building',
    }"
  />

  <!-- 👉 User Management -->
  <template v-if="isModuleEnabled('Student') || isModuleEnabled('Teacher') || isModuleEnabled('GuardianProfile')">
    <VerticalNavSectionTitle :item="{ heading: t('nav.user_management') }" />
    <VerticalNavLink
      v-if="isModuleEnabled('Student')"
      :item="{
        title: t('nav.students'),
        to: '/user-management/students',
        icon: 'bx-user',
      }"
    />
    <VerticalNavLink
      v-if="isModuleEnabled('Teacher')"
      :item="{
        title: t('nav.teachers'),
        to: '/user-management/teachers',
        icon: 'bx-briefcase-alt-2',
      }"
    />
    <VerticalNavLink
      v-if="isModuleEnabled('GuardianProfile')"
      :item="{
        title: t('nav.guardians'),
        to: '/user-management/guardians',
        icon: 'bx-group',
      }"
    />
  </template>

  <!-- 👉 Assignments -->
  <template v-if="isModuleEnabled('Assignment')">
    <VerticalNavSectionTitle :item="{ heading: t('nav.assignments') }" />
    <VerticalNavLink
      :item="{
        title: t('nav.assignments'),
        to: '/assignments/assignments',
        icon: 'bx-task',
      }"
    />
    <VerticalNavLink
      :item="{
        title: t('nav.studyMaterials'),
        to: '/assignments/study-materials',
        icon: 'bx-folder-open',
      }"
    />
  </template>

  <!-- 👉 Meetings -->
  <template v-if="isModuleEnabled('Meeting')">
    <VerticalNavSectionTitle :item="{ heading: t('nav.meetings') }" />
    <VerticalNavLink
      :item="{
        title: t('nav.meetings'),
        to: '/meetings/meetings',
        icon: 'bx-video',
      }"
    />
  </template>

  <!-- 👉 Communication -->
  <template v-if="isModuleEnabled('Communication')">
    <VerticalNavSectionTitle :item="{ heading: t('nav.communication') }" />
    <VerticalNavLink
      :item="{
        title: t('nav.messages'),
        to: '/communication/messages',
        icon: 'bx-message-detail',
      }"
    />
    <VerticalNavLink
      :item="{
        title: t('nav.emailBroadcast'),
        to: '/communication/email-broadcast',
        icon: 'bx-envelope',
      }"
    />
  </template>

  <!-- 👉 Attendance -->
  <template v-if="isModuleEnabled('Attendance')">
    <VerticalNavSectionTitle :item="{ heading: t('nav.attendance') }" />
    <VerticalNavLink
      :item="{
        title: t('nav.attendanceRecords'),
        to: '/attendance/records',
        icon: 'bx-calendar-check',
      }"
    />
  </template>

  <!-- 👉 Behavior -->
  <template v-if="isModuleEnabled('Behavior')">
    <VerticalNavSectionTitle :item="{ heading: t('nav.behavior') }" />
    <VerticalNavLink
      :item="{
        title: t('nav.behaviorRecords'),
        to: '/behavior/records',
        icon: 'bx-shield-quarter',
      }"
    />
    <VerticalNavLink
      :item="{
        title: t('nav.behaviorCategories'),
        to: '/behavior/categories',
        icon: 'bx-list-check',
      }"
    />
  </template>

  <!-- 👉 Examination -->
  <template v-if="isModuleEnabled('Examination')">
    <VerticalNavSectionTitle :item="{ heading: t('nav.examination') }" />
    <VerticalNavLink
      :item="{
        title: t('nav.exams'),
        to: '/examination/exams',
        icon: 'bx-file',
      }"
    />
    <VerticalNavLink
      :item="{
        title: t('nav.examGrades'),
        to: '/examination/grades',
        icon: 'bx-medal',
      }"
    />
  </template>

  <!-- 👉 Schedule -->
  <template v-if="isModuleEnabled('Timetable')">
    <VerticalNavSectionTitle :item="{ heading: t('nav.schedule') }" />
    <VerticalNavLink
      :item="{
        title: t('nav.periods'),
        to: '/schedule/periods',
        icon: 'bx-time-five',
      }"
    />
    <VerticalNavLink
      :item="{
        title: t('nav.timetables'),
        to: '/schedule/timetables',
        icon: 'bx-calendar-week',
      }"
    />
  </template>

  <!-- 👉 Settings -->
  <VerticalNavSectionTitle :item="{ heading: t('nav.settings') }" />
  <VerticalNavLink
    :item="{
      title: t('nav.branches'),
      to: '/settings/branches',
      icon: 'bx-store-alt',
    }"
  />
  <VerticalNavLink
    :item="{
      title: t('nav.languages'),
      to: '/settings/languages',
      icon: 'bx-globe',
    }"
  />
  <VerticalNavLink
    :item="{
      title: t('nav.generalSettings'),
      to: '/settings/general',
      icon: 'bx-cog',
    }"
  />
  <VerticalNavLink
    :item="{
      title: t('nav.organizationSettings'),
      to: '/settings/organization',
      icon: 'bx-sitemap',
    }"
  />
  <VerticalNavLink
    :item="{
      title: t('nav.moduleSettings'),
      to: '/settings/modules',
      icon: 'bx-grid-alt',
    }"
  />
  <VerticalNavLink
    :item="{
      title: t('nav.aiTranslationSettings'),
      to: '/settings/ai-translation',
      icon: 'bx-globe',
    }"
  />

  <!-- 👉 Account -->
  <VerticalNavSectionTitle :item="{ heading: t('nav.account') }" />
  <VerticalNavLink
    :item="{
      title: t('nav.accountSettings'),
      to: '/account-settings',
      icon: 'bx-user',
    }"
  />
</template>
