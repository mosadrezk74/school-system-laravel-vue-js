<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import PortalStatCard from '@modules/Portal/resources/js/components/PortalStatCard.vue'

defineOptions({ layout: TeacherLayout })

const props = defineProps({
  identity: { type: Object, required: true },
  widgets: { type: Array, default: () => [] },
})

const { t } = useI18n()

const tiles = computed(() => props.widgets.map(widget => ({
  ...widget,
  title: t(`portal.teacher.stats.${widget.id}`),
})))
</script>

<template>
  <Head :title="t('portal.teacher.title')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.greeting', { name: identity.name })"
        :subtitle="identity.subtitle ?? t('portal.teacher.subtitle')"
        icon="bx-chalkboard"
      >
        <template #actions>
          <VBtn
            prepend-icon="bx-list-check"
            href="/teacher/attendance"
            :as="Link"
          >
            {{ t('portal.teacher.quickAttendance') }}
          </VBtn>
        </template>
      </PortalHeader>
    </VCol>

    <VCol
      v-for="tile in tiles"
      :key="tile.id"
      cols="12"
      sm="6"
      lg="4"
    >
      <Link :href="tile.route" class="text-decoration-none">
        <PortalStatCard
          :title="tile.title"
          :value="tile.value"
          :icon="tile.icon"
          :color="tile.color"
        />
      </Link>
    </VCol>
  </VRow>
</template>
