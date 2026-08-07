<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import StudentLayout from '@modules/Portal/resources/js/layouts/student.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import PortalStatCard from '@modules/Portal/resources/js/components/PortalStatCard.vue'

defineOptions({ layout: StudentLayout })

const props = defineProps({
  identity: { type: Object, required: true },
  widgets: { type: Array, default: () => [] },
})

const { t } = useI18n()

const tiles = computed(() => props.widgets.map(widget => ({
  ...widget,
  title: t(`portal.student.stats.${widget.id}`),
  display: widget.id === 'attendance_rate' ? `${widget.value}%` : widget.value,
})))
</script>

<template>
  <Head :title="t('portal.student.title')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.greeting', { name: identity.name })"
        :subtitle="identity.subtitle ?? t('portal.student.subtitle')"
        icon="bx-book-reader"
      />
    </VCol>

    <VCol
      v-for="tile in tiles"
      :key="tile.id"
      cols="12"
      sm="6"
      lg="3"
    >
      <Link :href="tile.route" class="text-decoration-none">
        <PortalStatCard
          :title="tile.title"
          :value="tile.display"
          :icon="tile.icon"
          :color="tile.color"
        />
      </Link>
    </VCol>

    <VCol v-if="!tiles.length" cols="12">
      <VCard>
        <VCardText class="d-flex flex-column align-center pa-12 text-center text-disabled">
          <VIcon icon="bx-book-reader" size="48" class="mb-3" />
          {{ t('portal.comingSoon') }}
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
