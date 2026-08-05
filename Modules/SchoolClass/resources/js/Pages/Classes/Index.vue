<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  classes: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')

let searchTimer
watch(search, value => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/academic/classes', { search: value }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 350)
})

const headers = computed(() => [
  { title: t('classes.name'), key: 'name' },
  { title: t('classes.sectionCode'), key: 'section_code' },
  { title: t('classes.academicYear'), key: 'academic_year' },
  { title: t('classes.gradeLevel'), key: 'grade_level' },
  { title: t('classes.branch'), key: 'branch' },
  { title: t('classes.capacity'), key: 'capacity', sortable: false },
  { title: t('classes.active'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  if (!confirm(t('classes.confirmArchive')))
    return

  router.delete(`/academic/classes/${item.id}`, { preserveScroll: true })
}

function goToPage(page) {
  router.get('/academic/classes', { page, search: search.value }, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('classes.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('classes.title') }}</VCardTitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :to="'/academic/classes/create'"
          :href="'/academic/classes/create'"
          :as="Link"
        >
          {{ t('classes.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VCardText>
      <VTextField
        v-model="search"
        :label="t('classes.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 360px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="classes.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.academic_year="{ item }">
        {{ item.academic_year?.name }}
      </template>

      <template #item.grade_level="{ item }">
        {{ item.grade_level?.name }}
      </template>

      <template #item.branch="{ item }">
        {{ item.branch?.name }}
      </template>

      <template #item.capacity="{ item }">
        {{ item.current_enrollment }} / {{ item.capacity }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_active ? t('classes.active') : t('classes.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/academic/classes/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/academic/classes/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <div class="pa-8 text-center text-disabled">
          {{ t('classes.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="classes.meta && classes.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="classes.meta.current_page"
          :length="classes.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
