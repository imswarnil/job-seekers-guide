/**
 * Page meta this project adds on top of Nuxt's own.
 *
 * `definePageMeta` accepts anything at runtime, so without this the extra key
 * type-checks as an error on the page that sets it and as `unknown` on the
 * component that reads it. Declaring it keeps both ends honest.
 */
declare module 'vue-router' {
  interface RouteMeta {
    /**
     * Let the header run to the full width of the viewport instead of sitting
     * in the default container.
     *
     * Set on the pages that are themselves laid out wider than a container —
     * `/start` and everything on the learning path. See AppHeader.vue.
     */
    fluidHeader?: boolean
  }
}

export {}
