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
  classrooms: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const roomTypeFilter = ref(props.filters.room_type ?? null)
const isActiveFilter = ref(props.filters.is_active ?? null)

let searchTimer
watch(search, value => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    applyFilters()
  }, 350)
})

watch([roomTypeFilter, isActiveFilter], () => {
  applyFilters()
})

function applyFilters() {
  const params = {
    search: search.value || undefined,
    room_type: roomTypeFilter.value || undefined,
    is_active: isActiveFilter.value !== null ? isActiveFilter.value : undefined,
  }

  router.get('/academic/classrooms', params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

const headers = computed(() => [
  { title: t('classroom.fields.name'), key: 'name', sortable: false },
  { title: t('classroom.fields.room_type'), key: 'room_type' },
  { title: t('classroom.fields.capacity'), key: 'capacity' },
  { title: t('classroom.fields.is_active'), key: 'is_active', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const roomTypeLabels = {
  classroom: t('classroom.types.classroom'),
  lab: t('classroom.types.lab'),
  library: t('classroom.types.library'),
  auditorium: t('classroom.types.auditorium'),
  gym: t('classroom.types.gym'),
  music_room: t('classroom.types.music_room'),
  art_room: t('classroom.types.art_room'),
  computer_lab: t('classroom.types.computer_lab'),
  science_lab: t('classroom.types.science_lab'),
}

function archive(item) {
  askConfirm({
    message: t('classroom.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/academic/classrooms/${item.id}`, { preserveScroll: true })
    },
  })
}

function restore(item) {
  askConfirm({
    message: t('common.confirmRestore'),
    color: 'primary',
    onConfirm: () => {
      router.patch(`/academic/classrooms/${item.id}/restore`, {}, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  const params = {
    page,
    search: search.value || undefined,
    room_type: roomTypeFilter.value || undefined,
    is_active: isActiveFilter.value !== null ? isActiveFilter.value : undefined,
  }

  router.get('/academic/classrooms', params, {
    preserveState: true,
    preserveScroll: true,
  })
}

const filterOptions = {
  active: [
    { title: t('common.all'), value: null },
    { title: t('common.yes'), value: true },
    { title: t('common.no'), value: false },
  ],
}
</script>

<template>
  <Head :title="t('classroom.title')" />

  <AppPageHeader
    :title="t('classroom.title')"
    :subtitle="t('classroom.subtitle')"
  >
    <template #actions>
      <VBtn
        prepend-icon="bx-plus"
        :href="'/academic/classrooms/create'"
        :as="Link"
        color="primary"
      >
        {{ t('classroom.addNew') }}
      </VBtn>
    </template>
  </AppPageHeader>

  <AppDataTable
    :headers="headers"
    :items="classrooms.data"
    mobile-title-key="name"
    :meta="classrooms.meta"
    @page="goToPage"
  >
    <template #filters>
      <VRow>
        <VCol cols="12" md="3">
          <VTextField
            v-model="search"
            maxlength="100"
            :label="t('classroom.search')"
            prepend-inner-icon="bx-search"
            clearable
          />
        </VCol>

        <VCol cols="12" md="2">
          <VSelect
            v-model="isActiveFilter"
            :items="filterOptions.active"
            :label="t('classroom.fields.is_active')"
            clearable
          />
        </VCol>

        <VCol cols="12" md="2">
          <VBtn
            variant="tonal"
            color="secondary"
            size="small"
            @click="applyFilters"
          >
            {{ t('common.filter') }}
          </VBtn>
        </VCol>
      </VRow>
    </template>

      <template #item.name="{ item }">
        <AppTableLink :href="`/academic/classrooms/${item.id}`">
          {{ item.name ?? '—' }}
        </AppTableLink>
      </template>

      <template #item.room_type="{ item }">
        <VChip size="small">
          {{ roomTypeLabels[item.room_type] || item.room_type }}
        </VChip>
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
        <IconBtn :href="`/academic/classrooms/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn v-if="!item.deleted_at" @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
        <IconBtn v-else @click="restore(item)">
          <VIcon icon="bx-refresh" />
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
