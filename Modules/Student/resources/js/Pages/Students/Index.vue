<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  students: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
})

const { t } = useI18n()

const search = ref(props.filters.search ?? '')

let searchTimer
watch(search, value => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get('/user-management/students', { search: value }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 350)
})

const headers = computed(() => [
  { title: t('students.name'), key: 'name' },
  { title: t('students.email'), key: 'email' },
  { title: t('students.admissionNumber'), key: 'admission_number' },
  { title: t('students.academicYear'), key: 'academic_year' },
  { title: t('students.currentClass'), key: 'current_class' },
  { title: t('students.status'), key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function archive(item) {
  if (!confirm(t('students.confirmArchive')))
    return

  router.delete(`/user-management/students/${item.id}`, { preserveScroll: true })
}

function goToPage(page) {
  router.get('/user-management/students', { page, search: search.value }, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('students.title')" />

  <VCard>
    <VCardItem>
      <VCardTitle>{{ t('students.title') }}</VCardTitle>

      <template #append>
        <VBtn
          prepend-icon="bx-plus"
          :to="'/user-management/students/create'"
          :href="'/user-management/students/create'"
          :as="Link"
        >
          {{ t('students.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VCardText>
      <VTextField
        v-model="search"
        :label="t('students.search')"
        prepend-inner-icon="bx-search"
        clearable
        density="compact"
        style="max-inline-size: 360px;"
      />
    </VCardText>

    <VDataTable
      :headers="headers"
      :items="students.data"
      :items-per-page="-1"
      hide-default-footer
    >
      <template #item.academic_year="{ item }">
        {{ item.academic_year?.name }}
      </template>

      <template #item.current_class="{ item }">
        {{ item.current_class ? `${item.current_class.name} ${item.current_class.section_code}` : '—' }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'secondary'"
          size="small"
        >
          {{ item.is_active ? t('students.active') : t('students.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <IconBtn :href="`/user-management/students/${item.id}`" :as="Link">
          <VIcon icon="bx-show" />
        </IconBtn>
        <IconBtn :href="`/user-management/students/${item.id}/edit`" :as="Link">
          <VIcon icon="bx-edit" />
        </IconBtn>
        <IconBtn @click="archive(item)">
          <VIcon icon="bx-archive" />
        </IconBtn>
      </template>

      <template #no-data>
        <div class="pa-8 text-center text-disabled">
          {{ t('students.empty') }}
        </div>
      </template>
    </VDataTable>

    <template v-if="students.meta && students.meta.last_page > 1">
      <VDivider />
      <div class="d-flex justify-end pa-4">
        <VPagination
          :model-value="students.meta.current_page"
          :length="students.meta.last_page"
          :total-visible="5"
          density="compact"
          @update:model-value="goToPage"
        />
      </div>
    </template>
  </VCard>
</template>
