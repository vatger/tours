import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { renderToString } from '@vue/server-renderer'
import { createSSRApp, h } from 'vue'
import { type RouteName, route } from 'ziggy-js'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createServer(page =>
  createInertiaApp({
    page,
    render: renderToString,
    title: (title) => `${title} - ${appName}`,
    resolve: name => {
      const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
      return pages[`./Pages/${name}.vue`]
    },
    setup({ App, props, plugin }) {
      // Set up global route function for SSR
      // @ts-expect-error - Ziggy types
      global.route = <T extends RouteName>(name: T, params?: any, absolute?: boolean) =>
        route(name, params, absolute, {
          // @ts-expect-error - Ziggy types
          ...page.props.ziggy,
          // @ts-expect-error - Ziggy types
          location: new URL(page.props.ziggy.location),
        })

      const app = createSSRApp({
        render: () => h(App, props),
      })

      app.use(plugin)
      
      return app
    },
  }),
)