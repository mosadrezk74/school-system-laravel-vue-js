<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  branch: { type: Object, required: true },
})

const { t } = useI18n()

const infoCards = [
  { key: 'name', label: 'branch.fields.name', value: props.branch.name },
  { key: 'code', label: 'branch.fields.code', value: props.branch.code || '—' },
  { key: 'city', label: 'branch.fields.city', value: props.branch.city || '—' },
  { key: 'phone', label: 'branch.fields.phone', value: props.branch.phone || '—' },
  { key: 'address', label: 'branch.fields.address', value: props.branch.address || '—' },
]

const managementInfo = [
  { key: 'manager', label: 'branch.fields.manager_id', value: props.branch.manager?.name || '—' },
]
</script>

<template>
  <Head :title="branch.name" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ branch.name }}</VCardTitle>
          <template #append>
            <VChip :color="branch.is_active ? 'success' : 'secondary'" size="small">
              {{ branch.is_active ? t('common.active') : t('common.inactive') }}
            </VChip>
            <VChip v-if="branch.is_main" color="primary" size="small" class="ms-2">
              {{ t('branch.fields.is_main') }}
            </VChip>
            <IconBtn :href="`/settings/branches/${branch.id}/edit`" :as="Link" class="ms-2">
              <VIcon icon="bx-edit" />
            </IconBtn>
          </template>
        </VCardItem>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-building" />
          </template>
          <VCardTitle>{{ t('branch.sections.basic') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow>
            <VCol v-for="info in infoCards" :key="info.key" :cols="12" :md="6">
              <strong>{{ t(info.label) }}:</strong> {{ info.value }}
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-map-pin" />
          </template>
          <VCardTitle>{{ t('branch.sections.location') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow>
            <VCol v-for="loc in locationInfo" :key="loc.key" :cols="12" :md="6">
              <strong>{{ t(loc.label) }}:</strong> {{ loc.value }}
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-user" />
          </template>
          <VCardTitle>{{ t('branch.sections.management') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow>
            <VCol v-for="mgmt in managementInfo" :key="mgmt.key" :cols="12">
              <strong>{{ t(mgmt.label) }}:</strong> {{ mgmt.value }}
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

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
          <VList v-else>
            <VListItem
              v-for="dept in branch.departments"
              :key="dept.id"
            >
              <VListItemTitle>{{ dept.name }}</VListItemTitle>
              <VListItemSubtitle v-if="dept.code">{{ dept.code }}</VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>