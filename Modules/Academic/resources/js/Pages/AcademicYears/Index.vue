<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'
import AppPageHeader from '@/components/AppPageHeader.vue'
import AppDataTable from '@/components/AppDataTable.vue'
import AppDateField from '@/components/AppDateField.vue'

const props = defineProps({
  years: { type: Object, required: true },
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
    router.get('/academic/academic-years', { search: value }, {
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
  start_date: '',
  end_date: '',
  is_active: true,
  is_current: false,
})

// Computed, not a static array: t() called once would freeze the headers in
// whichever language was active when the page first mounted.
const headers = computed(() => [
  { title: t('academicYears.name'), key: 'name' },
  { title: t('academicYears.startDate'), key: 'start_date' },
  { title: t('academicYears.endDate'), key: 'end_date' },
  { title: t('academicYears.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function openCreate() {
  editing.value = null
  form.defaults({
    name: '', start_date: '', end_date: '',
    is_active: true, is_current: false,
  })
  form.reset()
  form.clearErrors()
  dialog.value = true
}

function openEdit(item) {
  editing.value = item
  form.defaults({
    name: item.name ?? '',
    start_date: item.start_date ?? '',
    end_date: item.end_date ?? '',
    is_active: item.is_active,
    is_current: item.is_current,
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
    form.put(`/academic/academic-years/${editing.value.id}`, options)
  else
    form.post('/academic/academic-years', options)
}

function setCurrent(item) {
  router.patch(`/academic/academic-years/${item.id}/set-current`, {}, { preserveScroll: true })
}

function remove(item) {
  askConfirm({
    message: t('academicYears.confirmDelete', { name: item.name }),
    color: 'error',
    onConfirm: () => {
      router.delete(`/academic/academic-years/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/academic/academic-years', { page, search: search.value }, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('academicYears.title')" />

  <AppPageHeader
    :title="t('academicYears.title')"
    :subtitle="t('academicYears.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        @click="openCreate"
      >
        {{ t('academicYears.create') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="years.data"
    :meta="years.meta"
    empty-icon="bx-calendar"
    :empty-title="t('academicYears.empty')"
    @page="goToPage"
  >
    <template #filters>
      <VTextField
        v-model="search"
        maxlength="100"
        :label="t('academicYears.search')"
        prepend-inner-icon="bx-search"
        clearable
      />
    </template>

      <template #item.status="{ item }">
        <VChip
          v-if="item.is_current"
          color="success"
          size="small"
        >
          {{ t('academicYears.current') }}
        </VChip>
        <VChip
          v-else-if="!item.is_active"
          color="secondary"
          size="small"
        >
          {{ t('academicYears.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn
          v-if="!item.is_current"
          :title="t('academicYears.setCurrent')"
          @click="setCurrent(item)"
        >
          <VIcon icon="bx-check-circle" />
        </IconBtn>
        <IconBtn @click="openEdit(item)">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn
          :disabled="item.is_current"
          @click="remove(item)"
        >
          <VIcon icon="bx-trash" />
        </IconBtn>
      </template>
  </AppDataTable>

  <VDialog
    v-model="dialog"
    max-width="560"
  >
    <VCard>
      <VCardItem>
        <VCardTitle>
          {{ editing ? t('academicYears.edit') : t('academicYears.create') }}
        </VCardTitle>
        <VCardSubtitle>
          {{ t('academicYears.dialogDescription') }}
        </VCardSubtitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="12"
          >
            <VTextField
              v-model="form.name"
              autocomplete="name"
              :label="required(t('academicYears.name'))"
              :error-messages="form.errors.name"
              maxlength="20"
              counter="20"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <AppDateField
              v-model="form.start_date"
              :label="required(t('academicYears.startDate'))"
              :error-messages="form.errors.start_date"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <AppDateField
              v-model="form.end_date"
              :label="required(t('academicYears.endDate'))"
              :error-messages="form.errors.end_date"
              required
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSwitch
              v-model="form.is_active"
              :label="t('academicYears.active')"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VSwitch
              v-model="form.is_current"
              :label="t('academicYears.current')"
            />
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
