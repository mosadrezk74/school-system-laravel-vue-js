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
  The auth screens no longer use the template's decorative corner shapes, so
  page-auth.scss (and the pile of overrides that existed only to stop those
  shapes from pushing the card off-screen) is gone with them. Centring is now
  done here in a few lines.
*/
.layout-wrapper.layout-blank {
  display: flex;
  flex-direction: column;

  /*
    The chain VApp → VMain → this wrapper must carry height down to
    .auth-wrapper, or its `align-center` has nothing to centre within.
  */
  min-block-size: 100%;

  .auth-wrapper {
    display: flex;
    flex: 1 1 auto;
    align-items: center;
    justify-content: center;
    min-block-size: 100dvh;
  }
}
</style>
