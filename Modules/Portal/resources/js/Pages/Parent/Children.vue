<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'

defineOptions({ layout: ParentLayout })

defineProps({
  children: { type: Array, default: () => [] },
})

const { t } = useI18n()
</script>

<template>
  <Head :title="t('portal.nav.children')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.children')"
        :subtitle="t('portal.parent.childrenSubtitle')"
        icon="bx-group"
      />
    </VCol>

    <VCol
      v-for="child in children"
      :key="child.id"
      cols="12"
      md="6"
    >
      <VCard>
        <VCardItem>
          <template #prepend>
            <VAvatar color="primary" variant="tonal" size="48">
              <VIcon icon="bx-user" />
            </VAvatar>
          </template>
          <VCardTitle>{{ child.name }}</VCardTitle>
          <VCardSubtitle>
            {{ child.class_name ?? '—' }}
            <span v-if="child.admission_number"> · {{ child.admission_number }}</span>
          </VCardSubtitle>
        </VCardItem>

        <VDivider />

        <VCardText class="d-flex flex-wrap gap-2">
          <VBtn
            variant="tonal"
            size="small"
            prepend-icon="bx-check-square"
            :href="`/parent/attendance?child=${child.id}`"
            :as="Link"
          >
            {{ t('portal.nav.attendance') }}
          </VBtn>
          <VBtn
            variant="tonal"
            size="small"
            prepend-icon="bx-task"
            :href="`/parent/homework?child=${child.id}`"
            :as="Link"
          >
            {{ t('portal.nav.homework') }}
          </VBtn>
          <VBtn
            variant="tonal"
            size="small"
            prepend-icon="bx-medal"
            :href="`/parent/grades?child=${child.id}`"
            :as="Link"
          >
            {{ t('portal.nav.grades') }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>

    <VCol v-if="!children.length" cols="12">
      <VCard>
        <VCardText class="d-flex flex-column align-center pa-12 text-center text-disabled">
          <VIcon icon="bx-group" size="48" class="mb-3" />
          {{ t('portal.parent.noChildren') }}
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
