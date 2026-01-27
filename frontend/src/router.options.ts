import type {RouterConfig} from '@nuxt/schema'

export default <RouterConfig>{
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || false
  },
}
