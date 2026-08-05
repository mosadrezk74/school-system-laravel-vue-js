/**
 * plugins/webfontloader.js
 *
 * webfontloader documentation: https://github.com/typekit/webfontloader
 */
export async function loadFonts() {
  const webFontLoader = await import(/* webpackChunkName: "webfontloader" */ 'webfontloader')

  /*
   * Alexandria covers Arabic and Latin in one family, so the interface keeps a
   * single voice when the locale switches instead of falling back to a
   * different face for Arabic text.
   */
  webFontLoader.load({
    google: {
      families: ['Alexandria:wght@300;400;500;600;700&display=swap'],
    },
  })
}
export default function () {
  loadFonts()
}
