<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import PortalHeader from '@modules/Portal/resources/js/components/PortalHeader.vue'
import PortalStatCard from '@modules/Portal/resources/js/components/PortalStatCard.vue'

defineOptions({ layout: ParentLayout })

const props = defineProps({
  identity: { type: Object, required: true },
  widgets: { type: Array, default: () => [] },
  hasChildren: { type: Boolean, default: false },
})

const { t } = useI18n()

const money = value => new Intl.NumberFormat(undefined, {
  style: 'currency',
  currency: 'SAR',
  minimumFractionDigits: 2,
}).format(Number(value ?? 0))

const tiles = computed(() => props.widgets.map(widget => ({
  ...widget,
  title: t(`portal.parent.stats.${widget.id}`),
  display: widget.id === 'attendance_rate'
    ? `${widget.value}%`
    : widget.id === 'outstanding_fees'
      ? money(widget.value)
      : widget.value,
})))
</script>

<template>
  <Head :title="t('portal.parent.title')" />

  <VRow>
    <VCol cols="12">
      <PortalHeader
        :title="t('portal.greeting', { name: identity.name })"
        :subtitle="t('portal.parent.subtitle')"
        icon="bx-group"
      />
    </VCol>

    <template v-if="hasChildren">
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
            :value="tile.display"
            :icon="tile.icon"
            :color="tile.color"
          />
        </Link>
      </VCol>
    </template>

    <VCol v-else cols="12">
      <VCard>
        <VCardText class="d-flex flex-column align-center pa-12 text-center text-disabled">
          <VIcon icon="bx-group" size="48" class="mb-3" />
          <p class="text-body-1 mb-0">
            {{ t('portal.parent.noChildren') }}
          </p>
          <p class="text-body-2 mb-0 mt-1">
            {{ t('portal.parent.noChildrenHint') }}
          </p>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
