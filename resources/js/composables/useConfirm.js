import { computed, ref } from 'vue'

/**
 * Drives <AppConfirmDialog> so a page can ask for confirmation in one call
 * instead of wiring up its own dialog state.
 *
 * Replaces the native `confirm()` that 41 pages used. Beyond the visual
 * mismatch, the browser dialog blocks the main thread, renders its buttons in
 * the *browser's* language rather than the app's, and always lays out
 * left-to-right inside an otherwise Arabic interface.
 *
 * Usage mirrors the shape of the code it replaces, so a call site stays a
 * one-liner:
 *
 *   const { ask, dialog, isOpen, run } = useConfirm()
 *   ask({ message: t('x.confirmArchive'), onConfirm: () => router.delete(url) })
 *
 * @returns {{ask: Function, dialog: object, isOpen: object, run: Function}}
 */
export function useConfirm() {
  const pending = ref(null)

  /**
   * @param {object} options
   * @param {string} options.message      Body copy — say what will happen.
   * @param {Function} options.onConfirm  Runs only when the user confirms.
   * @param {string} [options.title]
   * @param {string} [options.confirmText]
   * @param {string} [options.color]      `error` for destructive actions.
   */
  const ask = ({ message, onConfirm, title = null, confirmText = null, color = 'error' }) => {
    pending.value = { message, onConfirm, title, confirmText, color }
  }

  const run = () => {
    pending.value?.onConfirm?.()
    pending.value = null
  }

  /*
   * Writable so `v-model` on the dialog can close it: the dialog only ever
   * writes `false` (dismiss), which clears the pending action without
   * running it.
   */
  const isOpen = computed({
    get: () => pending.value !== null,
    set: value => {
      if (!value)
        pending.value = null
    },
  })

  const dialog = computed(() => pending.value ?? {})

  return { ask, run, isOpen, dialog }
}
