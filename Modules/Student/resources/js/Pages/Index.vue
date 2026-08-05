<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  rows: { type: Object, required: true },
})

const items = computed(() => props.rows.data ?? [])

const headers = computed(() => [
  { title: 'students.name', key: 'name' },
  { title: 'students.email', key: 'email' },
  { title: 'students.username', key: 'username' },
  { title: 'students.status', key: 'status', sortable: false },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

const deleteStudent = (id) => {
  if (confirm('Are you sure?')) {
    router.delete(`/user-management/students/${id}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <Head :title="$t('students.title')" />

  <VCard>
    <VCardItem class="d-flex justify-space-between align-center">
      <div>
        <VCardTitle>{{ $t('students.title') }}</VCardTitle>
        <VCardSubtitle class="mt-1">
          {{ $t('students.subtitle') }}
        </VCardSubtitle>
      </div>
      <template #append>
        <VBtn
          color="primary"
          prepend-icon="bx-plus"
          :tag="Link"
          href="/user-management/students/create"
        >
          {{ $t('students.addNew') }}
        </VBtn>
      </template>
    </VCardItem>

    <VDataTable
      :headers="headers"
      :items="items"
      :items-per-page="15"
    >
      <template #header.name>
        {{ $t('students.name') }}
      </template>
      <template #header.email>
        {{ $t('students.email') }}
      </template>
      <template #header.username>
        {{ $t('students.username') }}
      </template>
      <template #header.status>
        {{ $t('students.status') }}
      </template>

      <template #item.status="{ item }">
        <VChip
          :color="item.is_active ? 'success' : 'error'"
          size="small"
        >
          {{ item.is_active ? $t('students.active') : $t('students.inactive') }}
        </VChip>
      </template>

      <template #item.actions="{ item }">
        <VBtn
          icon="bx-edit"
          variant="text"
          size="small"
          color="warning"
          :tag="Link"
          :href="`/user-management/students/${item.id}/edit`"
        />
        <VBtn
          icon="bx-trash"
          variant="text"
          size="small"
          color="error"
          @click="deleteStudent(item.id)"
        />
      </template>
    </VDataTable>
  </VCard>
</template>
