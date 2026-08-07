<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'

const props = defineProps({
  notifications: { type: Object, required: true },
  unreadCount: { type: Number, default: 0 },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const unreadOnly = ref(Boolean(props.filters.unread))

watch(unreadOnly, value => {
  router.get('/notifications', value ? { unread: 1 } : {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
})

const items = computed(() => props.notifications.data ?? [])

function markRead(item) {
  router.patch(`/notifications/${item.id}/read`, {}, { preserveScroll: true })
}

function markAllRead() {
  router.patch('/notifications/read-all', {}, { preserveScroll: true })
}

function formatDateTime(value) {
  return value ? new Date(value).toLocaleString() : '—'
}

function goToPage(page) {
  router.get('/notifications', { ...(unreadOnly.value ? { unread: 1 } : {}), page }, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('notification.title')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('notification.title')"
        :subtitle="t('notification.subtitle')"
        icon="bx-bell"
      >
        <template #actions>
          <div class="d-flex align-center gap-3 flex-wrap">
            <VChip v-if="unreadCount" color="error" variant="tonal">
              {{ unreadCount }} {{ t('notification.unread') }}
            </VChip>
            <VSwitch
              v-model="unreadOnly"
              :label="t('notification.unread_only')"
              density="compact"
              hide-details
            />
            <VBtn
              v-if="unreadCount"
              variant="tonal"
              prepend-icon="bx-check-double"
              @click="markAllRead"
            >
              {{ t('notification.mark_all_read') }}
            </VBtn>
          </div>
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VList v-if="items.length" density="comfortable">
          <VListItem
            v-for="item in items"
            :key="item.id"
            :class="{ 'bg-surface-variant': !item.is_read }"
          >
            <template #prepend>
              <VAvatar
                :color="item.is_read ? 'secondary' : 'primary'"
                variant="tonal"
                size="40"
              >
                <VIcon :icon="item.icon" />
              </VAvatar>
            </template>

            <VListItemTitle class="text-wrap">
              {{ item.title ?? item.type }}
            </VListItemTitle>
            <VListItemSubtitle class="text-wrap">
              {{ item.body }}
            </VListItemSubtitle>
            <VListItemSubtitle class="text-caption mt-1">
              {{ formatDateTime(item.created_at) }}
            </VListItemSubtitle>

            <template #append>
              <div class="d-flex align-center gap-2">
                <VBtn
                  v-if="item.url"
                  variant="text"
                  size="small"
                  :href="item.url"
                >
                  {{ t('common.view') }}
                </VBtn>
                <IconBtn v-if="!item.is_read" @click="markRead(item)">
                  <VIcon icon="bx-check" />
                </IconBtn>
              </div>
            </template>
          </VListItem>
        </VList>

        <VCardText v-else class="d-flex flex-column align-center pa-12 text-center text-disabled">
          <VIcon icon="bx-bell-off" size="48" class="mb-3" />
          {{ t('notification.empty') }}
        </VCardText>

        <template v-if="notifications.meta && notifications.meta.last_page > 1">
          <VDivider />
          <div class="d-flex justify-end pa-4">
            <VPagination
              :model-value="notifications.meta.current_page"
              :length="notifications.meta.last_page"
              :total-visible="5"
              density="compact"
              @update:model-value="goToPage"
            />
          </div>
        </template>
      </VCard>
    </VCol>
  </VRow>
</template>
