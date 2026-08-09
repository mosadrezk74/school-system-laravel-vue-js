<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  targetType: { type: String, default: null },
  targetIds: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  classes: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  errors: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:targetType', 'update:targetIds'])

const { t } = useI18n()

const targetTypeOptions = computed(() => [
  { title: t('communication.audience.all'), value: 'all' },
  { title: t('communication.audience.role'), value: 'role' },
  { title: t('communication.audience.academicYear'), value: 'academic_year' },
  { title: t('communication.audience.class'), value: 'class' },
  { title: t('communication.audience.users'), value: 'users' },
])

const idOptions = computed(() => {
  switch (props.targetType) {
    case 'role':
      return props.roles
    case 'academic_year':
      return props.academicYears.map(y => ({ title: y.name, value: y.id }))
    case 'class':
      return props.classes.map(c => ({ title: c.name, value: c.id }))
    case 'users':
      return props.users.map(u => ({ title: u.name, value: u.id }))
    default:
      return []
  }
})

function setTargetType(value) {
  emit('update:targetType', value)
  emit('update:targetIds', [])
}
</script>

<template>
  <VRow>
    <VCol cols="12" md="5">
      <VSelect
        :model-value="targetType"
        :items="targetTypeOptions"
        :label="t('communication.fields.targetType')"
        :error-messages="errors.target_type"
        @update:model-value="setTargetType"
      />
    </VCol>

    <VCol v-if="targetType && targetType !== 'all'" cols="12" md="7">
      <VSelect
        :model-value="targetIds"
        :items="idOptions"
        :label="t('communication.fields.targetIds')"
        multiple
        chips
        closable-chips
        :error-messages="errors.target_ids"
        @update:model-value="value => emit('update:targetIds', value)"
      />
    </VCol>
  </VRow>
</template>
