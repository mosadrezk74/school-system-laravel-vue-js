<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  classroom: { type: Object, required: true },
})

const { t } = useI18n()

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

const basicInfo = [
  { key: 'name', label: 'classroom.fields.name', value: props.classroom.name },
  { key: 'code', label: 'classroom.fields.code', value: props.classroom.code || '—' },
  { key: 'room_type', label: 'classroom.fields.room_type', value: roomTypeLabels[props.classroom.room_type] || props.classroom.room_type || '—' },
  { key: 'capacity', label: 'classroom.fields.capacity', value: props.classroom.capacity || '—' },
]

const locationInfo = [
  { key: 'building', label: 'classroom.fields.building', value: props.classroom.building || '—' },
  { key: 'floor', label: 'classroom.fields.floor', value: props.classroom.floor !== null ? props.classroom.floor : '—' },
]

const facilitiesInfo = [
  { key: 'has_projector', label: 'classroom.fields.has_projector', value: props.classroom.has_projector ? '✓' : '✗' },
  { key: 'has_smartboard', label: 'classroom.fields.has_smartboard', value: props.classroom.has_smartboard ? '✓' : '✗' },
]
</script>

<template>
  <Head :title="classroom.name" />

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardItem>
          <VCardTitle>{{ classroom.name }}</VCardTitle>
          <template #append>
            <VChip :color="classroom.is_active ? 'success' : 'secondary'" size="small">
              {{ classroom.is_active ? t('common.active') : t('common.inactive') }}
            </VChip>
            <IconBtn :href="`/academic/classrooms/${classroom.id}/edit`" :as="Link" class="ms-2">
              <VIcon icon="bx-edit" />
            </IconBtn>
          </template>
        </VCardItem>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-info-circle" />
          </template>
          <VCardTitle>{{ t('classroom.sections.basic') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow>
            <VCol v-for="info in basicInfo" :key="info.key" :cols="12" :md="6">
              <strong>{{ t(info.label) }}:</strong> {{ info.value }}
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-map-pin" />
          </template>
          <VCardTitle>{{ t('classroom.sections.location') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow>
            <VCol v-for="info in locationInfo" :key="info.key" :cols="12" :md="6">
              <strong>{{ t(info.label) }}:</strong> {{ info.value }}
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-wrench" />
          </template>
          <VCardTitle>{{ t('classroom.sections.facilities') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VRow>
            <VCol v-for="info in facilitiesInfo" :key="info.key" :cols="12" :md="6">
              <strong>{{ t(info.label) }}:</strong> {{ info.value }}
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="6">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VIcon icon="bx-toggle-left" />
          </template>
          <VCardTitle>{{ t('classroom.sections.status') }}</VCardTitle>
        </VCardItem>
        <VCardText>
          <VChip :color="classroom.is_active ? 'success' : 'secondary'" size="large">
            {{ classroom.is_active ? t('common.active') : t('common.inactive') }}
          </VChip>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
