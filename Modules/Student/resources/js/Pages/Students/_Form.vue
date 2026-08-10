<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  form: { type: Object, required: true },
  classes: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

const { t } = useI18n()

// A trailing "*" on the label is the one cue every field needs, required or
// not — leaving it off already reads as optional, so there's no separate
// "Optional" marker to maintain in sync.
const required = label => `${label} *`

const classOptions = computed(() => props.classes.map(c => ({
  title: [c.name, c.grade_level?.name]
    .filter(Boolean)
    .join(' · '),
  value: c.id,
})))

const activeTab = ref('account')

// Field names per tab, used only to badge a tab when one of its fields has a
// validation error — the user shouldn't have to click through every tab to
// find out which one needs attention.
const tabFields = {
  account: ['name', 'email', 'username', 'password', 'gender', 'birth_date', 'national_id', 'phone', 'is_active'],
  enrollment: ['current_class_id', 'admission_date', 'admission_number', 'roll_number', 'is_graduated', 'scholarship_percentage'],
  guardians: ['father_name', 'father_phone', 'mother_name', 'mother_phone', 'guardian_name', 'guardian_phone', 'guardian_relation', 'address', 'city'],
  medical: ['blood_group', 'allergies', 'medical_conditions', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation'],
}

const tabHasError = tab => tabFields[tab].some(field => !!props.form.errors[field])
</script>

<template>
  <VTabs
    v-model="activeTab"
    show-arrows
    class="v-tabs-pill"
  >
    <VTab value="account">
      <VIcon size="20" start icon="bx-user" />
      {{ $t('students.sections.account') }}
      <VIcon v-if="tabHasError('account')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="enrollment">
      <VIcon size="20" start icon="bx-book-open" />
      {{ $t('students.sections.enrollment') }}
      <VIcon v-if="tabHasError('enrollment')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="guardians">
      <VIcon size="20" start icon="bx-group" />
      {{ $t('students.sections.guardians') }}
      <VIcon v-if="tabHasError('guardians')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="medical">
      <VIcon size="20" start icon="bx-first-aid" />
      {{ $t('students.sections.medical') }}
      <VIcon v-if="tabHasError('medical')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
  </VTabs>

  <VWindow v-model="activeTab" class="mt-6 disable-tab-transition">
    <VWindowItem value="account">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-user" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('students.sections.account') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('students.sections.accountDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.name"
            :label="required(t('students.name'))"
            :hint="$t('students.hints.name')"
            persistent-hint
            :error-messages="form.errors.name"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.email"
            type="email"
            :label="required(t('students.email'))"
            :hint="$t('students.hints.email')"
            persistent-hint
            :error-messages="form.errors.email"
            maxlength="50"
            :counter="50"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.username"
            :label="required(t('students.username'))"
            :hint="$t('students.hints.username')"
            persistent-hint
            :error-messages="form.errors.username"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.password"
            type="password"
            :label="isEdit ? t('students.password') : required(t('students.password'))"
            :hint="isEdit ? $t('students.hints.passwordHint') : $t('students.hints.password')"
            persistent-hint
            :error-messages="form.errors.password"
            :required="!isEdit"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VSelect
            v-model="form.gender"
            :items="[
              { title: $t('students.male'), value: 'male' },
              { title: $t('students.female'), value: 'female' },
            ]"
            :label="required(t('students.gender'))"
            :hint="$t('students.hints.gender')"
            persistent-hint
            :error-messages="form.errors.gender"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.birth_date"
            type="date"
            :label="$t('students.birthDate')"
            :hint="$t('students.hints.birthDate')"
            persistent-hint
            :error-messages="form.errors.birth_date"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.national_id"
            :label="$t('students.nationalId')"
            :hint="$t('students.hints.nationalId')"
            persistent-hint
            :error-messages="form.errors.national_id"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.phone"
            :label="$t('students.phone')"
            :hint="$t('students.hints.phone')"
            persistent-hint
            :error-messages="form.errors.phone"
          />
        </VCol>

        <VCol cols="12" md="6" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_active"
            :label="$t('students.active')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('students.hints.active') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="enrollment">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-book-open" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('students.sections.enrollment') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('students.sections.enrollmentDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="4">
          <VSelect
            v-model="form.current_class_id"
            clearable
            :items="classOptions"
            :label="$t('students.currentClass')"
            :hint="$t('students.hints.currentClass')"
            persistent-hint
            :error-messages="form.errors.current_class_id"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.admission_date"
            type="date"
            :label="required(t('students.admissionDate'))"
            :hint="$t('students.hints.admissionDate')"
            persistent-hint
            :error-messages="form.errors.admission_date"
            required
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.admission_number"
            :label="$t('students.admissionNumber')"
            :hint="$t('students.hints.admissionNumber')"
            persistent-hint
            :error-messages="form.errors.admission_number"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.roll_number"
            :label="$t('students.rollNumber')"
            :hint="$t('students.hints.rollNumber')"
            persistent-hint
            :error-messages="form.errors.roll_number"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.scholarship_percentage"
            type="number"
            min="0"
            max="100"
            step="0.01"
            suffix="%"
            :label="$t('students.scholarshipPercentage')"
            :hint="$t('students.hints.scholarshipPercentage')"
            persistent-hint
            :error-messages="form.errors.scholarship_percentage"
          />
        </VCol>

        <VCol cols="12" md="6" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_graduated"
            :label="$t('students.isGraduated')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('students.hints.isGraduated') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="guardians">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-group" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('students.sections.guardians') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('students.sections.guardiansDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.father_name"
            :label="$t('students.fatherName')"
            :hint="$t('students.hints.fatherName')"
            persistent-hint
            :error-messages="form.errors.father_name"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.father_phone"
            :label="$t('students.fatherPhone')"
            :hint="$t('students.hints.fatherPhone')"
            persistent-hint
            :error-messages="form.errors.father_phone"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.mother_name"
            :label="$t('students.motherName')"
            :hint="$t('students.hints.motherName')"
            persistent-hint
            :error-messages="form.errors.mother_name"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.mother_phone"
            :label="$t('students.motherPhone')"
            :hint="$t('students.hints.motherPhone')"
            persistent-hint
            :error-messages="form.errors.mother_phone"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.guardian_name"
            :label="$t('students.guardianName')"
            :hint="$t('students.hints.guardianName')"
            persistent-hint
            :error-messages="form.errors.guardian_name"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.guardian_phone"
            :label="$t('students.guardianPhone')"
            :hint="$t('students.hints.guardianPhone')"
            persistent-hint
            :error-messages="form.errors.guardian_phone"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.guardian_relation"
            :label="$t('students.guardianRelation')"
            :hint="$t('students.hints.guardianRelation')"
            persistent-hint
            :error-messages="form.errors.guardian_relation"
          />
        </VCol>

        <VCol cols="12" md="8">
          <VTextField
            v-model="form.address"
            :label="$t('students.address')"
            :hint="$t('students.hints.address')"
            persistent-hint
            :error-messages="form.errors.address"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.city"
            :label="$t('students.city')"
            :hint="$t('students.hints.city')"
            persistent-hint
            :error-messages="form.errors.city"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="medical">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-first-aid" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('students.sections.medical') }}
          </h6>
          <p class="text-body-2 text-medium-emphasis mb-0">
            {{ $t('students.sections.medicalDesc') }}
          </p>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="4">
          <VTextField
            v-model="form.blood_group"
            :label="$t('students.bloodGroup')"
            :hint="$t('students.hints.bloodGroup')"
            persistent-hint
            :error-messages="form.errors.blood_group"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.allergies"
            :label="$t('students.allergies')"
            :hint="$t('students.hints.allergies')"
            persistent-hint
            :error-messages="form.errors.allergies"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.medical_conditions"
            :label="$t('students.medicalConditions')"
            :hint="$t('students.hints.medicalConditions')"
            persistent-hint
            :error-messages="form.errors.medical_conditions"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.emergency_contact_name"
            :label="$t('students.emergencyContactName')"
            :hint="$t('students.hints.emergencyContactName')"
            persistent-hint
            :error-messages="form.errors.emergency_contact_name"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.emergency_contact_phone"
            :label="$t('students.emergencyContactPhone')"
            :hint="$t('students.hints.emergencyContactPhone')"
            persistent-hint
            :error-messages="form.errors.emergency_contact_phone"
          />
        </VCol>

        <VCol cols="12" md="4">
          <VTextField
            v-model="form.emergency_contact_relation"
            :label="$t('students.emergencyContactRelation')"
            :hint="$t('students.hints.emergencyContactRelation')"
            persistent-hint
            :error-messages="form.errors.emergency_contact_relation"
          />
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>

<style scoped>
/*
 * The field-level hint doubles as help text here, not a brief input format
 * note, so it needs to read clearly as secondary/quiet — smaller and greyer
 * than the label above it, matching the .text-caption/.text-medium-emphasis
 * pair already used for the switch captions in this same form.
 *
 * The template's own _field.scss colours the hint with the active theme
 * colour while a field is focused (v-messages inherits that colour), so
 * `color` has to be forced here too — opacity alone isn't enough to override
 * it back to a neutral grey. Scoped to :not(.v-input--error) so a real
 * validation message underneath still shows in the theme's error colour.
 */
:deep(.v-input:not(.v-input--error) .v-messages) {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.75rem;
  opacity: var(--v-medium-emphasis-opacity);
}
</style>
