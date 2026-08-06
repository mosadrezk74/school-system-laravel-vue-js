<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  classrooms: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

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
  { title: t('classroom.fields.name'), key: 'name' },
  { title: t('classroom.fields.code'), key: 'code' },
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
  if (!confirm(t('classroom.confirmArchive'))) return
  router.delete(`/academic/classrooms/${item.id}`, { preserveScroll: true })
}

function restore(item) {
  if (!confirm(t('common.confirmRestore'))) return
  router.patch(`/academic/classrooms/${item.id}/restore`, {}, { preserveScroll: true })
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

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('classroom.title') }}</VCardTitle>
      <VCardSubtitle>{{ t('classroom.subtitle') }}</VCardSubtitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :href="'/academic/classrooms/create'"
          :as="Link"
          color="primary"
        >
          {{ t('classroom.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VCardText>
      <VRow>
        <VCol cols="12" md="3">
          <VTextField
            v-model="search"
            :label="t('classroom.search')"
            prepend-inner-icon="bx-search"
            clearable
            density="compact"
          />
        </VCol>

        <VCol cols="12" md="2">
          <VSelect
            v-model="isActiveFilter"
            :items="filterOptions.active"
            :label="t('classroom.fields.is_active')"
            density="compact"
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
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="classrooms.data"
      :items-per-page="-1"
      hide-default-footer
    >
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
        <IconBtn :href="`/academic/classrooms/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
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

      <template #no-data>
        <div class="pa-8 text-center text-disabled">
          {{ t('classroom.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="classrooms.meta && classrooms.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="classrooms.meta.current_page"
          :length="classrooms.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
