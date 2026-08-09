<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TranslatableTextInput from '@/components/TranslatableTextInput.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  categories: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

// A trailing "*" on the label is the one cue every field needs, required or
// not — leaving it off already reads as optional, so there's no separate
// "Optional" marker to maintain in sync.
const required = label => `${label} *`

const search = ref(props.filters.search ?? '')

// Debounced so typing doesn't fire a visit per keystroke. preserveState keeps
// the field focused; replace avoids stacking history entries.
let searchTimer
watch(search, value => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/behavior/categories', { search: value }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 350)
})

const dialog = ref(false)
const editing = ref(null)

const form = useForm({
  name: '',
  code: '',
  type: 'violation',
  points: 0,
  is_active: true,
})

const headers = computed(() => [
  { title: t('behaviorCategories.name'), key: 'name' },
  { title: t('behaviorCategories.code'), key: 'code' },
  { title: t('behaviorCategories.type'), key: 'type' },
  { title: t('behaviorCategories.points'), key: 'points' },
  { title: t('behaviorCategories.active'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const typeOptions = computed(() => [
  { title: t('behaviorCategories.merit'), value: 'merit' },
  { title: t('behaviorCategories.violation'), value: 'violation' },
])

function openCreate() {
  editing.value = null
  form.defaults({
    name: '', code: '', type: 'violation', points: 0, is_active: true,
  })
  form.reset()
  form.clearErrors()
  dialog.value = true
}

function openEdit(item) {
  editing.value = item
  form.defaults({
    name: item.name ?? '',
    code: item.code ?? '',
    type: item.type ?? 'violation',
    points: item.points ?? 0,
    is_active: item.is_active,
  })
  form.reset()
  form.clearErrors()
  dialog.value = true
}

function save() {
  const options = {
    preserveScroll: true,
    onSuccess: () => { dialog.value = false },
  }

  if (editing.value)
    form.put(`/behavior/categories/${editing.value.id}`, options)
  else
    form.post('/behavior/categories', options)
}

function remove(item) {
  askConfirm({
    message: t('behaviorCategories.confirmDelete', { name: item.name }),
    color: 'error',
    onConfirm: () => {
      router.delete(`/behavior/categories/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/behavior/categories', { page, search: search.value }, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('behaviorCategories.title')" />

  <AppPageHeader
    :title="t('behaviorCategories.title')"
    :subtitle="t('behaviorCategories.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        @click="openCreate"
      >
        {{ t('behaviorCategories.create') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <VCard>

    <VCardText>
      <VTextField
        v-model="search"
        :label="t('behaviorCategories.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 320px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="categories.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.type="{ item }">
        <VChip
          :color="item.type === 'merit' ? 'success' : 'error'"
          size="small"
        >
          {{ item.type === 'merit' ? t('behaviorCategories.merit') : t('behaviorCategories.violation') }}
        </VChip>
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_active ? t('behaviorCategories.active') : t('behaviorCategories.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn @click="openEdit(item)">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="remove(item)">
          <VIcon icon="bx-trash" />
        </IconBtn>
      </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-shield-quarter"
          :title="t('behaviorCategories.empty')"
        />
      </template>
    </VDataTable>

    <AppPagination
      :meta="categories.meta"
      @change="goToPage"
    />
  </VCard>

  <VDialog
    v-model="dialog"
    max-width="560"
  >
    <VCard>
      <VCardItem>
        <VCardTitle>
          {{ editing ? t('behaviorCategories.edit') : t('behaviorCategories.create') }}
        </VCardTitle>
        <VCardSubtitle>
          {{ t('behaviorCategories.dialogDescription') }}
        </VCardSubtitle>
      </VCardItem>

      <VCardText>
        <TranslatableTextInput
          v-model="form.name"
          field="name"
          :label="t('behaviorCategories.name')"
          :errors="form.errors"
          required
          class="mb-4"
        />

        <VRow>
          <VCol cols="12" md="6">
            <VTextField
              v-model="form.code"
              :label="t('behaviorCategories.code')"
              :hint="t('behaviorCategories.hints.code')"
              persistent-hint
              :error-messages="form.errors.code"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.type"
              :items="typeOptions"
              :label="required(t('behaviorCategories.type'))"
              :hint="t('behaviorCategories.hints.type')"
              persistent-hint
              :error-messages="form.errors.type"
              required
            />
          </VCol>
          <VCol cols="12" md="6">
            <VTextField
              v-model.number="form.points"
              type="number"
              :label="required(t('behaviorCategories.points'))"
              :hint="t('behaviorCategories.hints.points')"
              persistent-hint
              :error-messages="form.errors.points"
              required
            />
          </VCol>
          <VCol cols="12">
            <VSwitch
              v-model="form.is_active"
              :label="t('behaviorCategories.active')"
            />
            <p class="text-caption text-medium-emphasis mb-0">
              {{ t('behaviorCategories.hints.active') }}
            </p>
          </VCol>
        </VRow>
      </VCardText>

      <VCardText class="d-flex justify-end gap-3">
        <VBtn
          variant="tonal"
          color="secondary"
          @click="dialog = false"
        >
          {{ t('common.cancel') }}
        </VBtn>
        <VBtn
          :loading="form.processing"
          @click="save"
        >
          {{ t('common.save') }}
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
