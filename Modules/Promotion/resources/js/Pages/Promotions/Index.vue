<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AppDataTable from '@/components/AppDataTable.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useIndexFilters } from '@/composables/useIndexFilters'

const props = defineProps({
  promotions: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  statusOptions: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { filters, goToPage } = useIndexFilters({
  url: '/user-management/promotions',
  initial: {
    search: props.filters.search ?? null,
    status: props.filters.status ?? null,
    from_academic_year_id: props.filters.from_academic_year_id ?? null,
  },
})

const headers = computed(() => [
  { title: t('promotions.fields.student'), key: 'student_name', sortable: false },
  { title: t('promotions.fields.from'), key: 'from', sortable: false },
  { title: t('promotions.fields.to'), key: 'to', sortable: false },
  { title: t('promotions.fields.status'), key: 'status', sortable: false },
  { title: t('promotions.fields.promotedBy'), key: 'promoted_by', sortable: false },
  { title: t('promotions.fields.promotedAt'), key: 'promoted_at', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const statusFilterOptions = computed(() => [
  { title: t('common.all'), value: null },
  ...props.statusOptions,
])

const yearFilterOptions = computed(() => [
  { title: t('common.all'), value: null },
  ...props.academicYears,
])

const pending = ref(null)

/*
 * Revert is only offered where the server said it is safe (`can_revert`);
 * the policy and the Action refuse it regardless of what is rendered.
 */
const askRevert = item => {
  pending.value = {
    message: t('promotions.confirmRevert', { name: item.student_name }),
    run: () => router.delete(`/user-management/promotions/${item.id}/revert`, { preserveScroll: true }),
  }
}

const isConfirmOpen = computed({
  get: () => pending.value !== null,
  set: value => { if (!value) pending.value = null },
})

const formatDate = value => (value ? new Date(value).toLocaleString() : '—')

const dash = value => value || '—'
</script>

<template>
  <Head :title="t('promotions.title')" />

  <AppPageHeader
    :title="t('promotions.title')"
    :subtitle="t('promotions.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-up-arrow-alt"
        href="/user-management/promotions/create"
        :as="Link"
        color="primary"
      >
        {{ t('promotions.startPromotion') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="promotions.data"
    :meta="promotions.meta"
    mobile-title-key="student_name"
    :empty-title="t('promotions.empty')"
    empty-icon="bx-up-arrow-alt"
    @page="goToPage"
  >
    <template #filters>
      <VRow dense>
        <VCol
          cols="12"
          md="4"
        >
          <VTextField
            v-model="filters.search.value"
            :label="t('promotions.search')"
            prepend-inner-icon="bx-search"
            clearable
          />
        </VCol>

        <VCol
          cols="12"
          md="3"
        >
          <VSelect
            v-model="filters.status.value"
            :items="statusFilterOptions"
            :label="t('promotions.fields.status')"
          />
        </VCol>

        <VCol
          cols="12"
          md="3"
        >
          <VSelect
            v-model="filters.from_academic_year_id.value"
            :items="yearFilterOptions"
            :label="t('promotions.fields.fromYear')"
          />
        </VCol>
      </VRow>
    </template>

    <template #item.student_name="{ item }">
      <span class="font-weight-medium">{{ dash(item.student_name) }}</span>
      <div
        v-if="item.admission_number"
        class="text-caption text-medium-emphasis"
      >
        {{ item.admission_number }}
      </div>
    </template>

    <template #item.from="{ item }">
      {{ dash(item.from_academic_year_name) }}
      <div class="text-caption text-medium-emphasis">
        {{ dash(item.from_class_name) }}
      </div>
    </template>

    <template #item.to="{ item }">
      {{ dash(item.to_academic_year_name) }}
      <div class="text-caption text-medium-emphasis">
        {{ dash(item.to_class_name) }}
      </div>
    </template>

    <template #item.status="{ item }">
      <VChip
        :color="item.status_color"
        size="small"
      >
        {{ item.status_label }}
      </VChip>
    </template>

    <template #item.promoted_by="{ item }">
      {{ dash(item.promoted_by) }}
    </template>

    <template #item.promoted_at="{ item }">
      {{ formatDate(item.promoted_at) }}
    </template>

    <template #item.actions="{ item }">
      <IconBtn
        v-if="item.can_revert"
        @click="askRevert(item)"
      >
        <VIcon icon="bx-undo" />
      </IconBtn>
    </template>

    <template #empty-action>
      <VBtn
        prepend-icon="bx-up-arrow-alt"
        href="/user-management/promotions/create"
        :as="Link"
        color="primary"
      >
        {{ t('promotions.startPromotion') }}
      </VBtn>
    </template>
  </AppDataTable>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :message="pending?.message"
    :confirm-text="t('promotions.revert')"
    color="warning"
    @confirm="pending?.run()"
  />
</template>
