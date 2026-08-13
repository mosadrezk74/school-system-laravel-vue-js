<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppFilterBar from '@/components/AppFilterBar.vue'
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import MeetingCard from '../../components/MeetingCard.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppDateField from '@/components/AppDateField.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  meetings: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },

  /** Filter schema published by the module's Query::filterSchema(). */
  filterDefinitions: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
})

const { t } = useI18n()

/*
 * Which filters this page offers — and which exist at all — comes from the
 * server schema; the page only decides where the bar is rendered.
 */
const { filters, activeKeys, goToPage, reset, clear } = useIndexFilters({
  url: '/meetings/meetings',
  initial: { search: null, ...props.filters },
  definitions: props.filterDefinitions,
})






</script>

<template>
  <Head :title="t('meetings.title')" />

  <AppPageHeader
    :title="t('meetings.title')"
    :subtitle="t('meetings.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :href="'/meetings/meetings/create'"
        :as="Link"
      >
        {{ t('meetings.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VDivider />

    <VCardText class="d-flex flex-column gap-4">
      <VTextField
        v-model="filters.search.value"
        maxlength="100"
        :label="t('meetings.search')"
        prepend-inner-icon="bx-search"
        clearable
      />

      <AppFilterBar
        :definitions="filterDefinitions"
        :filters="filters"
        :active-keys="activeKeys"
        @clear="clear"
        @clear-all="reset"
      />
    </VCardText>

    <VDivider />

    <VCardText>
      <VRow v-if="meetings.data.length">
        <VCol
          v-for="meeting in meetings.data"
          :key="meeting.id"
          cols="12"
          md="6"
          lg="4"
        >
          <MeetingCard :meeting="meeting" />
        </VCol>
      </VRow>

      <AppEmptyState v-else
          icon="bx-video"
          :title="t('meetings.empty')"
        />
    </VCardText>

    <AppPagination
      :meta="meetings.meta"
      @change="goToPage"
    />
  </VCard>
</template>
