import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, h } from 'vue'
import { registerPlugins } from '@core/utils/plugins'
import DefaultLayout from '@/layouts/default.vue'
import TeacherLayout from '@modules/Portal/resources/js/layouts/teacher.vue'
import ParentLayout from '@modules/Portal/resources/js/layouts/parent.vue'
import StudentLayout from '@modules/Portal/resources/js/layouts/student.vue'

/**
 * Shells keyed by the `auth.portal` value the server shares; anything not
 * listed here (owner/admin, whose portal is null) gets the admin sidebar.
 */
const portalLayouts = {
  teacher: TeacherLayout,
  parent: ParentLayout,
  student: StudentLayout,
}

// Styles
import '@core-scss/template/index.scss'
import '@layouts/styles/index.scss'
import '@styles/styles.scss'

createInertiaApp({
  title: title => (title ? `${title} — ${import.meta.env.VITE_APP_NAME ?? 'Athr'}` : 'Athr'),

  resolve: name => {
    /*
     * Pages come from two places, so a module stays self-contained:
     *   "Dashboard"              → resources/js/Pages/Dashboard.vue
     *   "Academic::AcademicYears/Index"
     *                            → Modules/Academic/resources/js/Pages/AcademicYears/Index.vue
     * The "Module::" prefix mirrors Laravel's own view-namespace syntax.
     */
    const appPages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    const modulePages = import.meta.glob('../../Modules/*/resources/js/Pages/**/*.vue', { eager: true })

    let page
    if (name.includes('::')) {
      const [module, path] = name.split('::')

      page = modulePages[`../../Modules/${module}/resources/js/Pages/${path}.vue`]
    } else {
      page = appPages[`./Pages/${name}.vue`]
    }

    if (!page)
      throw new Error(`Inertia page not found: ${name}`)

    /*
     * Pages opt out of the shell by exporting `layout: null` (auth screens),
     * and may pin a specific one. Anything else gets the shell that matches
     * the signed-in account's portal, so a page shared by several roles
     * (notifications, for one) never shows a teacher the admin sidebar.
     */
    page.default.layout = page.default.layout === undefined
      ? (render, currentPage) => {
          const layout = portalLayouts[currentPage.props?.auth?.portal] ?? DefaultLayout

          return render(layout, () => currentPage)
        }
      : page.default.layout

    return page
  },

  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })

    app.use(plugin)

    // Registers Vuetify, i18n, pinia and iconify from resources/js/plugins.
    registerPlugins(app)

    app.mount(el)
  },

  progress: { color: '#7367F0' },
})
