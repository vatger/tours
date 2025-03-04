import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { renderToString } from '@vue/server-renderer'
import { createSSRApp, h } from 'vue'
import { route as ziggyRoute } from 'ziggy-js'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createServer((page) =>
  createInertiaApp({
    page,
    render: renderToString,
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ App, props, plugin }) {
      // Configure Ziggy for SSR
      const ziggyConfig = {
        ...page.props.ziggy,
        location: new URL(page.props.ziggy.location),
      }

      // Create route function
      const route = (name: string, params?: any, absolute?: boolean) =>
        ziggyRoute(name, params, absolute, ziggyConfig)

      // Make route function available globally for SSR
      if (typeof window === 'undefined') {
        global.route = route
      }

      // Create the SSR app with route function in context
      const app = createSSRApp({
        render: () => h(App, props),
        setup() {
          return {
            route,
          }
        },
      })

      app.use(plugin)
      
      return app
    },
  }),
)