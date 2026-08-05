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

  <!-- 👉 Student Management -->
  <VerticalNavSectionTitle :item="{ heading: t('nav.user_management') }" />
  <VerticalNavLink
    :item="{
      title: t('nav.students'),
      to: '/user-management/students',
      icon: 'bx-user',
    }"
  />

  <!-- 👉 Settings -->
  <VerticalNavSectionTitle :item="{ heading: t('nav.settings') }" />
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
