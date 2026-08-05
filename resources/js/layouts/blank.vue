<template>
  <VApp>
    <VMain>
      <div
        class="layout-wrapper layout-blank"
        data-allow-mismatch
      >
        <slot />
      </div>
    </VMain>
  </VApp>
</template>

<style lang="scss">
/*
  Sneat loaded this per-page through the old vue-router setup, so nothing
  imported it after the move to Inertia — leaving the decorative auth shapes
  in normal flow, where they stacked ~900px above the card and pushed it
  off-screen. The blank layout owns the auth screens, so it imports them here.
*/
@use "@core-scss/template/pages/page-auth";

.layout-wrapper.layout-blank {
  display: flex;
  flex-direction: column;

  /*
    The chain VApp → VMain → this wrapper must carry height down to
    .auth-wrapper, or its `align-center` has nothing to centre within and the
    card pins to the top. page-auth.scss sets 100dvh on .auth-wrapper, which
    would stack with VMain's own viewport height — so cap it at 100% and let
    each ancestor pass the height through.
  */
  min-block-size: 100%;

  .auth-wrapper {
    flex: 1 1 auto;
    min-block-size: 100%;
  }
}
</style>
