<script setup>
/**
 * The family-facing report card.
 *
 * Declares no layout of its own: the same page serves the student and the
 * guardian portal, so it inherits whichever shell the signed-in user has —
 * the pattern the notifications and terms pages already use.
 *
 * Only published results reach this page; the server applies that rule in
 * ReportCardQuery, never the markup below.
 */
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ReportCardView from '@modules/Examination/resources/js/components/ReportCardView.vue'
import ChildSelector from '@modules/Portal/resources/js/components/ChildSelector.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'

defineProps({
  card: { type: Object, default: null },
  children: { type: Array, default: () => [] },
  selectedChild: { type: [Number, null], default: null },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('reportCard.title')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader :title="t('reportCard.title')">
        <template #actions>
          <ChildSelector
            v-if="children.length"
            :children="children"
            :selected="selectedChild"
            url="/parent/report-card"
          />
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <ReportCardView
        v-if="card"
        :card="card"
      />

      <AppEmptyState
        v-else
        :title="t('reportCard.empty')"
        :description="t('reportCard.emptyHint')"
        icon="bx-medal"
      />
    </VCol>
  </VRow>
</template>
