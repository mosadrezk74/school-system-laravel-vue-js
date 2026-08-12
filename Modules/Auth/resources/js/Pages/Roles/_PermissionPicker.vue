<script setup>
/**
 * The permission grid, one expansion panel per module.
 *
 * The backend already groups by the `module` column and splits each name into
 * resource + ability, so this only has to render that shape and keep the
 * selected-name array in sync. Selection is always a flat array of permission
 * names because that is exactly what the Action syncs to the pivot.
 */
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  /** [{ module, permissions: [{ id, name, resource, ability }] }] */
  groups: { type: Array, required: true },

  /** Flat array of selected permission names (v-model). */
  modelValue: { type: Array, default: () => [] },

  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const { t } = useI18n()

const selected = computed({
  get: () => props.modelValue ?? [],
  set: value => emit('update:modelValue', value),
})

const namesIn = group => group.permissions.map(permission => permission.name)

const selectedCount = group =>
  namesIn(group).filter(name => selected.value.includes(name)).length

const isAllSelected = group => selectedCount(group) === group.permissions.length

const isPartiallySelected = group =>
  selectedCount(group) > 0 && !isAllSelected(group)

/*
 * Toggling a module adds or removes that module's names only — a Set keeps
 * the operation order-independent and free of duplicates when two modules
 * happen to share a resource name.
 */
function toggleGroup(group) {
  const names = namesIn(group)
  const next = new Set(selected.value)

  if (isAllSelected(group))
    names.forEach(name => next.delete(name))
  else
    names.forEach(name => next.add(name))

  selected.value = [...next]
}

function toggleAll(shouldSelect) {
  selected.value = shouldSelect
    ? props.groups.flatMap(namesIn)
    : []
}

const totalCount = computed(() => props.groups.flatMap(namesIn).length)

/** Resource rows within a module, so abilities line up per resource. */
const resourceRows = group => {
  const rows = new Map()

  group.permissions.forEach(permission => {
    if (!rows.has(permission.resource))
      rows.set(permission.resource, [])

    rows.get(permission.resource).push(permission)
  })

  return [...rows.entries()].map(([resource, permissions]) => ({ resource, permissions }))
}
</script>

<template>
  <div>
    <div class="d-flex flex-wrap align-center justify-space-between gap-2 mb-4">
      <div>
        <h6 class="text-h6 mb-0">
          {{ t('roles.fields.permissions') }}
        </h6>
        <p class="text-body-2 text-medium-emphasis mb-0">
          {{ t('roles.selectedCount', { selected: selected.length, total: totalCount }) }}
        </p>
      </div>

      <div class="d-flex gap-2">
        <VBtn
          size="small"
          variant="tonal"
          :disabled="disabled"
          @click="toggleAll(true)"
        >
          {{ t('roles.selectAll') }}
        </VBtn>
        <VBtn
          size="small"
          variant="tonal"
          color="secondary"
          :disabled="disabled"
          @click="toggleAll(false)"
        >
          {{ t('roles.clearAll') }}
        </VBtn>
      </div>
    </div>

    <VExpansionPanels multiple>
      <VExpansionPanel
        v-for="group in groups"
        :key="group.module"
      >
        <VExpansionPanelTitle>
          <div class="d-flex align-center gap-3">
            <VCheckbox
              :model-value="isAllSelected(group)"
              :indeterminate="isPartiallySelected(group)"
              :disabled="disabled"
              hide-details
              density="compact"
              @click.stop="toggleGroup(group)"
            />
            <span class="font-weight-medium">{{ group.module }}</span>
            <VChip size="x-small">
              {{ selectedCount(group) }}/{{ group.permissions.length }}
            </VChip>
          </div>
        </VExpansionPanelTitle>

        <VExpansionPanelText>
          <div
            v-for="row in resourceRows(group)"
            :key="row.resource"
            class="mb-3"
          >
            <p class="text-body-2 font-weight-medium mb-1">
              {{ row.resource }}
            </p>
            <div class="d-flex flex-wrap gap-4">
              <VCheckbox
                v-for="permission in row.permissions"
                :key="permission.id"
                v-model="selected"
                :value="permission.name"
                :label="permission.ability"
                :disabled="disabled"
                hide-details
                density="compact"
              />
            </div>
          </div>
        </VExpansionPanelText>
      </VExpansionPanel>
    </VExpansionPanels>
  </div>
</template>
