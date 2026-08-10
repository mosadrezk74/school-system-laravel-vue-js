<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  form: { type: Object, required: true },
  subjects: { type: Array, default: () => [] },
  isEdit: { type: Boolean, default: false },
})

const { t } = useI18n()

const required = label => `${label} *`

const activeTab = ref('account')

const tabFields = {
  account: ['name', 'email', 'username', 'password', 'gender', 'birth_date', 'national_id', 'phone', 'is_active'],
  professional: ['employee_id', 'qualification', 'specialization', 'experience_years'],
  employment: ['joining_date', 'position', 'salary_type', 'salary_base'],
  subjects: ['subject_ids'],
}

const tabHasError = tab => tabFields[tab].some(field => !!props.form.errors[field])

const salaryTypes = computed(() => [
  { title: t('teacher.salaryTypes.fixed'), value: 'fixed' },
  { title: t('teacher.salaryTypes.hourly'), value: 'hourly' },
  { title: t('teacher.salaryTypes.contract'), value: 'contract' },
])

const subjectsOptions = computed(() =>
  props.subjects.map(subject => ({
    title: subject.name,
    value: subject.id,
  }))
)
</script>

<template>
  <VTabs
    v-model="activeTab"
    show-arrows
    class="v-tabs-pill"
  >
    <VTab value="account">
      <VIcon size="20" start icon="bx-user" />
      {{ $t('teacher.sections.account') }}
      <VIcon v-if="tabHasError('account')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="professional">
      <VIcon size="20" start icon="bx-briefcase" />
      {{ $t('teacher.sections.professional') }}
      <VIcon v-if="tabHasError('professional')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="employment">
      <VIcon size="20" start icon="bx-money" />
      {{ $t('teacher.sections.employment') }}
      <VIcon v-if="tabHasError('employment')" size="16" end icon="bx-error-circle" color="error" />
    </VTab>
    <VTab value="subjects">
      <VIcon size="20" start icon="bx-book-open" />
      {{ $t('teacher.sections.subjects') }}
      <VIcon v-if="tabHasError('subjects')" size="16" end icon="bx-error-circle" color="error" />
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
            {{ $t('teacher.sections.account') }}
          </h6>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.name"
            :label="required(t('teacher.fields.name'))"
            :hint="$t('teacher.hints.name')"
            persistent-hint
            :error-messages="form.errors.name"
            maxlength="255"
            :counter="255"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.email"
            type="email"
            :label="required(t('teacher.fields.email'))"
            :hint="$t('teacher.hints.email')"
            persistent-hint
            :error-messages="form.errors.email"
            maxlength="255"
            :counter="255"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.username"
            :label="required(t('teacher.fields.username'))"
            :hint="$t('teacher.hints.username')"
            persistent-hint
            :error-messages="form.errors.username"
            maxlength="100"
            :counter="100"
            required
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.password"
            type="password"
            :label="isEdit ? t('teacher.fields.password') : required(t('teacher.fields.password'))"
            :hint="isEdit ? $t('teacher.hints.passwordHint') : $t('teacher.hints.password')"
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
            :label="required(t('teacher.fields.gender'))"
            :hint="$t('teacher.hints.gender')"
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
            maxlength="14"
            :counter="14"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.phone"
            :label="$t('students.phone')"
            persistent-hint
            :error-messages="form.errors.phone"
            maxlength="20"
            :counter="20"
          />
        </VCol>

        <VCol cols="12" md="6" class="d-flex flex-column justify-center">
          <VSwitch
            v-model="form.is_active"
            :label="$t('teacher.fields.is_active')"
          />
          <p class="text-caption text-medium-emphasis mb-0">
            {{ $t('teacher.hints.is_active') }}
          </p>
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="professional">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-briefcase" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('teacher.sections.professional') }}
          </h6>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.employee_id"
            :label="$t('teacher.fields.employee_id')"
            :hint="$t('teacher.hints.employee_id')"
            persistent-hint
            :error-messages="form.errors.employee_id"
            maxlength="50"
            :counter="50"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.qualification"
            :label="$t('teacher.fields.qualification')"
            :hint="$t('teacher.hints.qualification')"
            persistent-hint
            :error-messages="form.errors.qualification"
            maxlength="255"
            :counter="255"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.specialization"
            :label="$t('teacher.fields.specialization')"
            :hint="$t('teacher.hints.specialization')"
            persistent-hint
            :error-messages="form.errors.specialization"
            maxlength="255"
            :counter="255"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.experience_years"
            type="number"
            min="0"
            max="50"
            :label="$t('teacher.fields.experience_years')"
            :hint="$t('teacher.hints.experience_years')"
            persistent-hint
            :error-messages="form.errors.experience_years"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="employment">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-money" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('teacher.sections.employment') }}
          </h6>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="6">
          <VTextField
            v-model="form.joining_date"
            type="date"
            :label="$t('teacher.fields.joining_date')"
            :hint="$t('teacher.hints.joining_date')"
            persistent-hint
            :error-messages="form.errors.joining_date"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.position"
            :label="$t('teacher.fields.position')"
            :hint="$t('teacher.hints.position')"
            persistent-hint
            :error-messages="form.errors.position"
            maxlength="100"
            :counter="100"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VSelect
            v-model="form.salary_type"
            clearable
            :items="salaryTypes"
            :label="$t('teacher.fields.salary_type')"
            :hint="$t('teacher.hints.salary_type')"
            persistent-hint
            :error-messages="form.errors.salary_type"
          />
        </VCol>

        <VCol cols="12" md="6">
          <VTextField
            v-model="form.salary_base"
            type="number"
            step="0.01"
            min="0"
            :label="$t('teacher.fields.salary_base')"
            :hint="$t('teacher.hints.salary_base')"
            persistent-hint
            :error-messages="form.errors.salary_base"
          />
        </VCol>
      </VRow>
    </VWindowItem>

    <VWindowItem value="subjects">
      <div class="d-flex align-center gap-3 mb-6">
        <VAvatar color="primary" variant="tonal" size="40">
          <VIcon icon="bx-book-open" />
        </VAvatar>
        <div>
          <h6 class="text-h6">
            {{ $t('teacher.sections.subjects') }}
          </h6>
        </div>
      </div>

      <VRow>
        <VCol cols="12" md="8">
          <VSelect
            v-model="form.subject_ids"
            multiple
            chips
            :items="subjectsOptions"
            :label="$t('teacher.fields.subject_ids')"
            :hint="$t('teacher.hints.subject_ids')"
            persistent-hint
            :error-messages="form.errors.subject_ids"
          />
        </VCol>
      </VRow>
    </VWindowItem>
  </VWindow>
</template>

<style scoped>
:deep(.v-input:not(.v-input--error) .v-messages) {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.75rem;
  opacity: var(--v-medium-emphasis-opacity);
}
</style>