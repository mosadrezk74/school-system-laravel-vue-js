<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  years: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

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
  code: '',
  start_date: '',
  end_date: '',
  is_active: true,
  is_current: false,
})

// Computed, not a static array: t() called once would freeze the headers in
// whichever language was active when the page first mounted.
const headers = computed(() => [
  { title: t('academicYears.name'), key: 'name' },
  { title: t('academicYears.code'), key: 'code' },
  { title: t('academicYears.startDate'), key: 'start_date' },
  { title: t('academicYears.endDate'), key: 'end_date' },
  { title: t('academicYears.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function openCreate() {
  editing.value = null
  form.defaults({
    name: '', code: '', start_date: '', end_date: '',
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
    code: item.code ?? '',
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
  if (!confirm(t('academicYears.confirmDelete', { name: item.name })))
    return

  router.delete(`/academic/academic-years/${item.id}`, { preserveScroll: true })
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('academicYears.title') }}</VCardTitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          @click="openCreate"
        >
          {{ t('academicYears.create') }}
        </VBtn>
      </template>
    </VCardItem>

    <VCardText>
      <VTextField
        v-model="search"
        :label="t('academicYears.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 320px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="years.data"
      :items-per-page="-1"
      hide-default-footer
    >
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

      <template #no-data>
        <div class="pa-8 text-center text-disabled">
          {{ t('academicYears.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="years.meta && years.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="years.meta.current_page"
          :length="years.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>

  <VDialog
    v-model="dialog"
    max-width="560"
  >
    <VCard>
      <VCardItem>
        <VCardTitle>
          {{ editing ? t('academicYears.edit') : t('academicYears.create') }}
        </VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.name"
              :label="t('academicYears.name')"
              :error-messages="form.errors.name"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.code"
              :label="t('academicYears.code')"
              :error-messages="form.errors.code"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.start_date"
              type="date"
              :label="t('academicYears.startDate')"
              :error-messages="form.errors.start_date"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="form.end_date"
              type="date"
              :label="t('academicYears.endDate')"
              :error-messages="form.errors.end_date"
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
</template>
