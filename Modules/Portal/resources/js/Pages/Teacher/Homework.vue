<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import AppPagination from '@/components/AppPagination.vue'
import AppEmptyState from '@/components/AppEmptyState.vue'
import AppConfirmDialog from '@/components/AppConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm'

defineOptions({ layout: TeacherLayout })

const props = defineProps({
  assignments: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  classes: { type: Array, default: () => [] },
})

const { t } = useI18n()

const { ask: askConfirm, run: runConfirm, isOpen: isConfirmOpen, dialog: confirmDialog } = useConfirm()

const search = ref(props.filters.search ?? '')
const classId = ref(props.filters.class_id ?? null)

const classOptions = computed(() => props.classes.map(c => ({ title: c.name, value: c.id })))

function buildQuery(extra = {}) {
  const query = { search: search.value, ...extra }

  if (classId.value)
    query.class_id = classId.value

  return query
}

function applyFilters() {
  router.get('/teacher/homework', buildQuery(), {
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

watch(classId, applyFilters)

const headers = computed(() => [
  { title: t('portal.teacher.homeworkTitle'), key: 'title' },
  { title: t('portal.teacher.deadline'), key: 'deadline' },
  { title: t('portal.teacher.maxScore'), key: 'max_score', align: 'end' },
  { title: t('portal.teacher.published'), key: 'published_at' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
])

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString() : '—'
}

function archive(item) {
  askConfirm({
    message: t('portal.teacher.confirmArchive'),
    color: 'error',
    onConfirm: () => {
      router.delete(`/teacher/homework/${item.id}`, { preserveScroll: true })
    },
  })
}

function goToPage(page) {
  router.get('/teacher/homework', buildQuery({ page }), {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head :title="t('portal.nav.homework')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.nav.homework')"
        :subtitle="t('portal.teacher.homeworkSubtitle')"
        icon="bx-task"
      >
        <template #actions>
          <VBtn prepend-icon="bx-plus" href="/teacher/homework/create" :as="Link">
            {{ t('portal.teacher.newHomework') }}
          </VBtn>
        </template>
      </PortalHeader>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText class="d-flex flex-wrap gap-4">
          <VTextField
            v-model="search"
            maxlength="100"
            :label="t('portal.teacher.searchHomework')"
            prepend-inner-icon="bx-search"
            density="compact"
            clearable
            hide-details
            style="max-inline-size: 320px; flex: 1 1 240px;"
          />

          <VSelect
            v-model="classId"
            :items="classOptions"
            :label="t('portal.teacher.selectClass')"
            density="compact"
            clearable
            hide-details
            style="max-inline-size: 220px;"
          />
        </VCardText>

        <VDataTable
          :headers="headers"
          :items="assignments.data"
          :items-per-page="-1"
          hide-default-footer
        >
          <template #item.deadline="{ item }">
            {{ formatDate(item.deadline) }}
          </template>

          <template #item.published_at="{ item }">
            <VChip
              :color="item.published_at ? 'success' : 'secondary'"
              size="small"
              variant="tonal"
            >
              {{ item.published_at ? t('portal.teacher.publishedYes') : t('portal.teacher.publishedNo') }}
            </VChip>
          </template>

          <template #item.actions="{ item }">
            <IconBtn :href="`/teacher/homework/${item.id}/edit`" :as="Link">
              <VIcon icon="bx-edit" />
            </IconBtn>
            <IconBtn @click="archive(item)">
              <VIcon icon="bx-archive" />
            </IconBtn>
          </template>

      <template #no-data>
        <AppEmptyState
          icon="bx-task"
          :title="t('portal.teacher.noHomework')"
        />
      </template>
        </VDataTable>

    <AppPagination
      :meta="assignments.meta"
      @change="goToPage"
    />
      </VCard>
    </VCol>
  </VRow>

  <AppConfirmDialog
    v-model="isConfirmOpen"
    :title="confirmDialog.title"
    :message="confirmDialog.message"
    :confirm-text="confirmDialog.confirmText"
    :color="confirmDialog.color ?? 'error'"
    @confirm="runConfirm"
  />
</template>
