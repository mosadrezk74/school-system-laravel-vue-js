<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  branch: { type: Object, required: true },
})

const { t } = useI18n()

const quickActions = [
  { key: 'edit', icon: 'bx-edit', href: `/settings/branches/${props.branch.id}/edit` },
  { key: 'viewClasses', icon: 'bx-chalkboard', href: `/academic/classes?branch_id=${props.branch.id}` },
]
</script>

<template>
  <Head :title="branch.name" />

  <VRow>
    <!-- 👉 Header banner -->
    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap align-center gap-4">
          <VAvatar color="primary" variant="tonal" size="56">
            <VIcon icon="bx-building" size="32" />
          </VAvatar>

          <div class="flex-grow-1">
            <div class="d-flex align-center gap-3 flex-wrap">
              <h5 class="text-h5 mb-0">
                {{ branch.name }}
              </h5>
              <VChip :color="branch.is_active ? 'success' : 'secondary'" size="small">
                {{ branch.is_active ? t('common.active') : t('common.inactive') }}
              </VChip>
              <VChip v-if="branch.is_main" color="primary" size="small" variant="tonal">
                {{ t('branch.fields.is_main') }}
              </VChip>
            </div>
            <p class="text-body-2 text-medium-emphasis mb-0 mt-1">
              {{ branch.code ?? '—' }} · {{ branch.city ?? '—' }}
            </p>
          </div>

          <VBtn
            prepend-icon="bx-edit"
            :href="`/settings/branches/${branch.id}/edit`"
            :as="Link"
          >
            {{ t('common.edit') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Basic Information -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-building" />
          </template>
          <VCardTitle>{{ t('branch.sections.basic') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('branch.fields.name') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ branch.name }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('branch.fields.code') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ branch.code ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Location -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-map-pin" />
          </template>
          <VCardTitle>{{ t('branch.sections.location') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow no-gutters>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('branch.fields.city') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ branch.city ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="6">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('branch.fields.phone') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ branch.phone ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
            <VCol cols="12">
              <VListItem>
                <VListItemTitle class="text-medium-emphasis text-caption">
                  {{ t('branch.fields.address') }}
                </VListItemTitle>
                <VListItemSubtitle class="text-body-1 text-high-emphasis">
                  {{ branch.address ?? '—' }}
                </VListItemSubtitle>
              </VListItem>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <!-- 👉 Management -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-user" />
          </template>
          <VCardTitle>{{ t('branch.sections.management') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VList density="compact" class="pa-0">
            <VListItem>
              <VListItemTitle class="text-medium-emphasis text-caption">
                {{ t('branch.fields.manager_id') }}
              </VListItemTitle>
              <VListItemSubtitle class="text-body-1 text-high-emphasis">
                {{ branch.manager?.name ?? '—' }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
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
          <VCardTitle>{{ t('branch.quickActions') }}</VCardTitle>
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
            <VListItemTitle>{{ t(`branch.quickAction.${action.key}`) }}</VListItemTitle>
          </VListItem>
        </VList>
      </VCard>
    </VCol>

    <!-- 👉 Departments -->
    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-group" />
          </template>
          <VCardTitle>{{ t('branch.sections.departments') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <div v-if="!branch.departments || branch.departments.length === 0" class="text-disabled">
            {{ t('common.noItems') }}
          </div>
          <VList v-else class="pa-0">
            <VListItem
              v-for="dept in branch.departments"
              :key="dept.id"
            >
              <VListItemTitle>{{ dept.name }}</VListItemTitle>
              <VListItemSubtitle v-if="dept.code">
                {{ dept.code }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
