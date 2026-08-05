<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  record: { type: Object, required: true },
})

const { t } = useI18n()

const quickActions = [
  { key: 'edit', icon: 'bx-edit', href: `/behavior/records/${props.record.id}/edit` },
  { key: 'viewStudent', icon: 'bx-user', href: props.record.student ? `/user-management/students/${props.record.student.id}` : null },
]
</script>

<template>
  <Head :title="t('behaviorRecords.view')" />

  <VRow>
    <!-- 👉 Header banner -->
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar
            :color="record.behavior_category?.type === 'merit' ? 'success' : 'error'"
            variant="tonal"
            size="56"
          >
            <VIcon icon="bx-shield-quarter" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ record.behavior_category?.name }}
              </h5>
              <VChip
                :color="record.status === 'reviewed' ? 'success' : 'warning'"
                size="small"
              >
                {{ record.status === 'reviewed' ? t('behaviorRecords.reviewed') : t('behaviorRecords.reported') }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ record.student?.name ?? '—' }} · {{ record.date }} ·
              {{ record.points >= 0 ? '+' : '' }}{{ record.points }} {{ t('behaviorRecords.points') }}
            </p>
          </div>

          <VBtn
            prepend-icon="bx-edit"
            :href="`/behavior/records/${record.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Record Details -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-shield-quarter" />
          </template>
          <VCardTitle>{{ t('behaviorRecords.sections.details') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('behaviorRecords.student') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  <Link
                    v-if="record.student"
                    :href="`/user-management/students/${record.student.id}`"
                  >
                    {{ record.student.name }}
                  </Link>
                  <span v-else>—</span>
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('behaviorRecords.category') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ record.behavior_category?.name ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('behaviorRecords.date') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ record.date }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('behaviorRecords.points') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ record.points }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('behaviorRecords.reportedBy') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ record.reported_by_user?.name ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('behaviorRecords.reviewedBy') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ record.reviewed_by_user?.name ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="12">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('behaviorRecords.description') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ record.description ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Quick Actions -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-bolt-circle" />
          </template>
          <VCardTitle>{{ t('behaviorRecords.quickActions') }}</VCardTitle>
        </VCardItem>
        <VList density="compact" class="pa-0">
          <VListItem
            v-for="action in quickActions"
            :key="action.key"
            :disabled="!action.href"
            :href="action.href"
            :as="action.href ? Link : 'div'"
          >
            <template #prepend>
              <VIcon :icon="action.icon" size="20" />
            </template>
            <VListItemTitle>{{ t(`behaviorRecords.quickAction.${action.key}`) }}</VListItemTitle>
          </VListItem>
        </VList>
      </VCard>
    </VCol>
  </VRow>
</template>
