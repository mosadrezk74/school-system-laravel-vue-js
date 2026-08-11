<script setup>
import { onBeforeUnmount, onMounted, ref, shallowRef } from 'vue'
import { useI18n } from 'vue-i18n'

/*
 * Wraps the official Jitsi External API for our self-hosted deployment.
 *
 * The script is loaded from the same instance that hosts the conference
 * (https://{domain}/external_api.js), which is the supported integration path
 * for a self-hosted Jitsi — not a bare <iframe>, because we need lifecycle
 * events and a controlled teardown.
 */
const props = defineProps({
  domain: { type: String, required: true },
  roomName: { type: String, required: true },
  displayName: { type: String, default: '' },
  email: { type: String, default: '' },
  // Moderators get the room opened without the prejoin gate.
  isModerator: { type: Boolean, default: false },
  lang: { type: String, default: 'en' },

  /*
   * Media defaults are a school setting, not a role consequence. They start
   * muted so nobody is pushed live before they have checked themselves, and
   * the prejoin screen stays on for everyone — including the teacher — so the
   * camera is never opened without an explicit click.
   */
  startWithAudioMuted: { type: Boolean, default: true },
  startWithVideoMuted: { type: Boolean, default: true },
  showPrejoin: { type: Boolean, default: true },
})

const emit = defineEmits(['joined', 'left', 'participant-joined', 'participant-left', 'failed', 'closed'])

const { t } = useI18n()

const container = ref(null)
// shallowRef: the Jitsi API object is a large external instance and must not
// be made deeply reactive, or Vue's proxying interferes with it.
const api = shallowRef(null)
const status = ref('loading')
const scriptPromises = new Map()

/**
 * Loads external_api.js once per domain. Repeated mounts reuse the same
 * promise instead of injecting duplicate <script> tags.
 */
function loadExternalApi(domain) {
  if (window.JitsiMeetExternalAPI)
    return Promise.resolve(window.JitsiMeetExternalAPI)

  if (scriptPromises.has(domain))
    return scriptPromises.get(domain)

  const promise = new Promise((resolve, reject) => {
    const script = document.createElement('script')

    script.src = `https://${domain}/external_api.js`
    script.async = true
    script.onload = () => window.JitsiMeetExternalAPI
      ? resolve(window.JitsiMeetExternalAPI)
      : reject(new Error('Jitsi External API unavailable'))
    script.onerror = () => reject(new Error('Failed to load Jitsi External API'))

    document.head.appendChild(script)
  })

  scriptPromises.set(domain, promise)

  return promise
}

function disposeApi() {
  if (!api.value) return

  try {
    api.value.dispose()
  } catch {
    // A disposed/blocked iframe can throw; teardown must not surface an error.
  }

  api.value = null
}

async function start() {
  status.value = 'loading'
  disposeApi()

  /*
   * Cameras and microphones are only exposed in a secure context, so over
   * plain HTTP navigator.mediaDevices is undefined and Jitsi throws deep
   * inside its own bundle while enumerating devices — leaving the embed
   * spinning forever with nothing actionable on screen. Fail loudly instead.
   */
  if (!window.isSecureContext) {
    status.value = 'insecure'
    emit('failed', new Error('Jitsi requires a secure context (HTTPS)'))

    return
  }

  try {
    const JitsiMeetExternalAPI = await loadExternalApi(props.domain)

    if (!container.value) return

    api.value = new JitsiMeetExternalAPI(props.domain, {
      roomName: props.roomName,
      parentNode: container.value,
      lang: props.lang,
      userInfo: {
        displayName: props.displayName,
        email: props.email,
      },
      configOverwrite: {
        prejoinPageEnabled: props.showPrejoin,
        startWithAudioMuted: props.startWithAudioMuted,
        startWithVideoMuted: props.startWithVideoMuted,
        disableDeepLinking: true,
      },
      interfaceConfigOverwrite: {
        MOBILE_APP_PROMO: false,
        SHOW_JITSI_WATERMARK: false,
        SHOW_BRAND_WATERMARK: false,
      },
    })

    /*
     * The overlay must come down as soon as Jitsi owns the iframe, not when
     * the conference is joined. A non-moderator lands on the prejoin screen
     * and only joins after clicking it — waiting for videoConferenceJoined
     * left that screen hidden behind an opaque overlay forever, so the
     * meeting looked like it never loaded.
     */
    api.value.getIFrame()?.addEventListener('load', () => {
      if (status.value === 'loading') status.value = 'ready'
    })

    api.value.addEventListener('videoConferenceJoined', () => {
      status.value = 'joined'
      emit('joined')
    })
    api.value.addEventListener('videoConferenceLeft', () => emit('left'))
    api.value.addEventListener('participantJoined', payload => emit('participant-joined', payload))
    api.value.addEventListener('participantLeft', payload => emit('participant-left', payload))
    api.value.addEventListener('readyToClose', () => {
      disposeApi()
      emit('closed')
    })
  } catch (error) {
    status.value = 'error'
    emit('failed', error)
  }
}

function retry() {
  start()
}

function hangUp() {
  api.value?.executeCommand('hangup')
}

defineExpose({ retry, hangUp })

onMounted(start)
onBeforeUnmount(disposeApi)
</script>

<template>
  <div class="jitsi-wrapper">
    <div v-show="status !== 'error' && status !== 'insecure'" ref="container" class="jitsi-frame" />

    <div v-if="status === 'loading'" class="jitsi-overlay d-flex flex-column align-center justify-center">
      <VProgressCircular indeterminate color="primary" size="42" />
      <p class="text-body-2 text-medium-emphasis mt-4 mb-0">
        {{ t('meetings.jitsi.loading') }}
      </p>
    </div>

    <div v-if="status === 'insecure'" class="jitsi-error d-flex flex-column align-center justify-center text-center">
      <VIcon icon="bx-lock-alt" size="42" class="mb-3 text-warning" />
      <p class="text-body-1 mb-0">
        {{ t('meetings.jitsi.insecure') }}
      </p>
    </div>

    <div v-if="status === 'error'" class="jitsi-error d-flex flex-column align-center justify-center text-center">
      <VIcon icon="bx-video-off" size="42" class="mb-3 text-medium-emphasis" />
      <p class="text-body-1 mb-4">
        {{ t('meetings.jitsi.failed') }}
      </p>
      <VBtn prepend-icon="bx-refresh" @click="retry">
        {{ t('meetings.jitsi.retry') }}
      </VBtn>
    </div>
  </div>
</template>

<style scoped>
.jitsi-wrapper {
  position: relative;
  overflow: hidden;
  border-radius: 8px;
  background: rgb(var(--v-theme-surface));
  block-size: min(72dvh, 720px);
  inline-size: 100%;
}

/* Jitsi injects its own iframe here and owns everything inside it. */
.jitsi-frame,
.jitsi-frame :deep(iframe) {
  border: 0;
  block-size: 100%;
  inline-size: 100%;
}

.jitsi-overlay,
.jitsi-error {
  position: absolute;
  padding: 1.5rem;
  background: rgb(var(--v-theme-surface));
  inset: 0;
}

@media (max-width: 600px) {
  .jitsi-wrapper {
    block-size: min(60dvh, 480px);
  }
}
</style>
