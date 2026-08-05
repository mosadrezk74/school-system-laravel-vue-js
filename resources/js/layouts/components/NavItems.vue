<script setup>
import { useI18n } from 'vue-i18n'
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'

const { t } = useI18n()
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
    :item="{
      title: t('nav.classes'),
      to: '/academic/classes',
      icon: 'bx-chalkboard',
    }"
  />

  <!-- 👉 Student Management -->
  <VerticalNavSectionTitle :item="{ heading: t('nav.user_management') }" />
  <VerticalNavLink
    :item="{
      title: t('nav.students'),
      to: '/user-management/students',
      icon: 'bx-user',
    }"
  />
  <VerticalNavLink
    :item="{
      title: t('nav.guardians'),
      to: '/user-management/guardians',
      icon: 'bx-group',
    }"
  />

  <!-- 👉 Behavior -->
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

  <!-- 👉 Settings -->
  <VerticalNavSectionTitle :item="{ heading: t('nav.settings') }" />
  
  <!-- 🔴 NEW: Branches Link Added Here -->
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