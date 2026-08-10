<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppTableLink from '@/components/AppTableLink.vue'

const props = defineProps({
  paymentMethods: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  types: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const type = ref(props.filters.type ?? null)

const typeOptions = computed(() => props.types.map(item => ({ title: item.label, value: item.value })))

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (type.value)
    query.type = type.value

  return query
}

function applyFilters() {
  router.get('/finance/payment-methods', buildQuery(), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

let searchTimer
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 350)
})

watch(type, applyFilters)

const headers = computed(() => [
  { title: t('finance.fields.name'), key: 'name' },
  { title: t('finance.fields.code'), key: 'code' },
  { title: t('finance.fields.type'), key: 'type' },
  { title: t('finance.fields.display_order'), key: 'display_order' },
  { title: t('finance.fields.is_active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  askConfirm({
    message: t('finance.payment_methods_archive_confirm'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/finance/payment-methods/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/finance/payment-methods', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('finance.payment_methods_title')" />

  <AppPageHeader
    :title="t('finance.payment_methods_title')"
    :subtitle="t('finance.payment_methods_subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        href="/finance/payment-methods/create"
        :as="Link"
      >
        {{ t('finance.add_payment_method') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="paymentMethods.data"
    mobile-title-key="name"
    :meta="paymentMethods.meta"
    empty-icon="bx-credit-card"
    :empty-title="t('finance.payment_methods_empty')"
    @page="goToPage"
  >
    <template #filters>
      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="search"
            :label="t('finance.search_payment_methods')"
            prepend-inner-icon="bx-search"
            clearable
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="type"
            :label="t('finance.fields.type')"
            :items="typeOptions"
            clearable
          />
        </VCol>
      </VRow>
    </template>

    <template #item.name="{ item }">
      <AppTableLink :href="`/finance/payment-methods/${item.id}`">
        {{ item.name ?? '—' }}
      </AppTableLink>
    </template>

    <template #item.type="{ item }">
      <div class="d-flex align-center gap-2">
        <span>{{ item.type_label ?? '—' }}</span>

        <!--
          An online method with no link sends the parent nowhere. The list is
          where an admin scans for what is misconfigured, so the gap is flagged
          here rather than only being discovered by a parent.
        -->
        <VTooltip
          v-if="item.type === 'online' && !item.payment_url"
          :text="t('finance.payment_url_missing')"
        >
          <template #activator="{ props: tooltipProps }">
            <VIcon
              v-bind="tooltipProps"
              icon="bx-error-circle"
              color="warning"
              size="18"
            />
          </template>
        </VTooltip>
      </div>
    </template>

    <template #item.is_active="{ item }">
      <VChip
        :color="item.is_active ? 'success' : 'secondary'"
        size="small"
      >
        {{ item.is_active ? t('common.active') : t('common.inactive') }}
      </VChip>
    </template>

    <template #item.actions="{ item }">
      <IconBtn :href="`/finance/payment-methods/${item.id}/edit`" :as="Link">
        <VIcon icon="bx-edit" />
      </IconBtn>
      <IconBtn @click="archive(item)">
        <VIcon icon="bx-archive" />
      </IconBtn>
    </template>
  </AppDataTable>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
