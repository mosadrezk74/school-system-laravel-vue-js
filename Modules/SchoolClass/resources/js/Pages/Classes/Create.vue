<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AppPageHeader from '@/components/AppPageHeader.vue'

const props = defineProps({
  gradeLevels: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
})

const { t } = useI18n()

const required = label => `${label} *`

const gradeLevelOptions = computed(() => props.gradeLevels.map(g => ({ title: g.name, value: g.id })))
const classroomOptions = computed(() => props.classrooms.map(c => ({ title: c.name, value: c.id })))

/*
 * The grade level is chosen once for the whole batch, and each row is one
 * section under it. Repeating the grade per row would let the rows disagree
 * with each other; the server takes it from the top level and ignores
 * anything a row might claim.
 */
const form = useForm({
  grade_level_id: null,
  sections: [newSection()],
})

function newSection() {
  return { name: '', capacity: 30, classroom_id: null, is_active: true }
}

function addSection() {
  form.sections.push(newSection())
}

function removeSection(index) {
  form.sections.splice(index, 1)
}

/** Errors arrive keyed by path, e.g. `sections.1.name`. */
const sectionError = (index, field) => form.errors[`sections.${index}.${field}`]

/*
 * A duplicate inside the form is worth flagging before the round trip, but the
 * server re-checks it — this only saves the user a failed submit.
 */
const duplicateNames = computed(() => {
  const seen = new Map()

  form.sections.forEach(section => {
    const key = section.name?.trim().toLowerCase()
    if (key)
      seen.set(key, (seen.get(key) ?? 0) + 1)
  })

  return new Set([...seen.entries()].filter(([, count]) => count > 1).map(([key]) => key))
})

const isDuplicate = section => duplicateNames.value.has(section.name?.trim().toLowerCase())

function submit() {
  form.post('/academic/classes/sections')
}
</script>

<template>
  <Head :title="t('classes.addNew')" />

  <AppPageHeader
    :title="t('classes.addNew')"
    :subtitle="t('classes.subtitle')"
  />

  <VCard class="app-form-card">
    <VCardText>
      <form @submit.prevent="submit">
        <div class="d-flex align-center gap-3 mb-6">
          <VAvatar color="primary" variant="tonal" size="40">
            <VIcon icon="bx-chalkboard" />
          </VAvatar>
          <div>
            <h6 class="text-h6">
              {{ t('classes.sections.details') }}
            </h6>
            <p class="text-body-2 text-medium-emphasis mb-0">
              {{ t('classes.sectionsHint') }}
            </p>
          </div>
        </div>

        <VRow>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.grade_level_id"
              :items="gradeLevelOptions"
              :label="required(t('classes.gradeLevel'))"
              :hint="t('classes.hints.gradeLevel')"
              persistent-hint
              :error-messages="form.errors.grade_level_id"
              required
            />
          </VCol>
        </VRow>

        <VDivider class="my-6" />

        <div class="d-flex align-center justify-space-between mb-4">
          <h6 class="text-h6 mb-0">
            {{ t('classes.sectionsTitle') }}
          </h6>
          <VChip size="small" variant="tonal">
            {{ form.sections.length }}
          </VChip>
        </div>

        <!--
          One card per section. Cards rather than table rows so the fields can
          stack on a phone instead of being squeezed into four narrow columns.
        -->
        <div class="class-sections">
          <VCard
            v-for="(section, index) in form.sections"
            :key="index"
            variant="outlined"
            class="class-sections__item"
          >
            <VCardText>
              <VRow align="center">
                <VCol cols="12" md="4">
                  <VTextField
                    v-model="section.name"
                    :label="required(t('classes.sectionName'))"
                    :error-messages="sectionError(index, 'name')
                      || (isDuplicate(section) ? t('classes.duplicateSectionName') : undefined)"
                  />
                </VCol>

                <VCol cols="12" md="2">
                  <VTextField
                    v-model.number="section.capacity"
                    type="number"
                    min="1"
                    :label="required(t('classes.capacity'))"
                    :error-messages="sectionError(index, 'capacity')"
                  />
                </VCol>

                <VCol cols="12" md="3">
                  <VSelect
                    v-model="section.classroom_id"
                    :items="classroomOptions"
                    :label="t('classes.classroom')"
                    clearable
                    :error-messages="sectionError(index, 'classroom_id')"
                  />
                </VCol>

                <VCol cols="12" md="2">
                  <VSwitch
                    v-model="section.is_active"
                    :label="section.is_active ? t('common.active') : t('common.inactive')"
                    hide-details
                  />
                </VCol>

                <VCol cols="12" md="1" class="d-flex justify-end">
                  <IconBtn
                    :disabled="form.sections.length === 1"
                    :title="t('classes.removeSection')"
                    @click="removeSection(index)"
                  >
                    <VIcon icon="bx-trash" color="error" />
                  </IconBtn>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </div>

        <VBtn
          variant="tonal"
          prepend-icon="bx-plus"
          class="mt-4"
          @click="addSection"
        >
          {{ t('classes.addSection') }}
        </VBtn>
      </form>
    </VCardText>

    <VCardActions class="app-form-actions">
      <VSpacer />
      <VBtn
        variant="tonal"
        color="secondary"
        :href="'/academic/classes'"
        :as="Link"
      >
        {{ t('common.cancel') }}
      </VBtn>
      <VBtn
        :loading="form.processing"
        :disabled="!form.grade_level_id || form.sections.length === 0"
        @click="submit"
      >
        {{ t('classes.createClasses') }}
      </VBtn>
    </VCardActions>
  </VCard>
</template>

<style lang="scss" scoped>
.class-sections {
  display: flex;
  flex-direction: column;
  gap: var(--app-space-3);

  &__item {
    /* Logical inline start so the accent sits on the reading edge in both
       Arabic and English rather than always on the left. */
    border-inline-start: 3px solid rgb(var(--v-theme-primary));
  }
}
</style>
