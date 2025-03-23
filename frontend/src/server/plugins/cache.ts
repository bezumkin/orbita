import {defineNitroPlugin} from 'nitropack/runtime'
import {type H3Event} from 'h3'

export const cacheTime = Number(useRuntimeConfig().CACHE_PAGES_TIME) || 0
export const cookieName = 'auth:token'

export function getKey(event: H3Event): string {
  let path = event.path.substring(1)

  // Handle Nuxt error page
  if (/^__nuxt_error/.test(path)) {
    const url = URL.parse(path, 'https://localhost/')?.searchParams?.get('url')
    if (url) {
      path = url.substring(1)
    }
  }

  return 'routes:' + (!path ? 'index' : path.split('/').join(':'))
}

export function getStatusCode(event: H3Event): number {
  const path = event.path.substring(1)
  if (/^__nuxt_error/.test(path)) {
    const tmp = URL.parse(path, 'https://localhost/')?.searchParams?.get('statusCode')
    if (tmp) {
      return Number(tmp)
    }
  }

  return Number(event.node.res.statusCode)
}

export function isEnabled(event: H3Event) {
  if (!cacheTime) {
    return false
  }
  const path = event.path.substring(1)
  const cookie = getCookie(event, cookieName)
  const allCache = /^(sitemap|rss|turbo)/

  return allCache.test(path) || (!cookie && !/^socket\.io/.test(path))
}

export default defineNitroPlugin(async (nitroApp) => {
  const storage = useStorage('cache')
  // Clear old cache on app restart
  const keys = await storage.getKeys('routes:')
  keys.forEach((key: string) => {
    storage.removeItem(key)
  })

  let time = 0

  nitroApp.hooks.hook('request', () => {
    time = new Date().getTime()
  })

  nitroApp.hooks.hook('beforeResponse', (event) => {
    setResponseHeader(event, 'X-Response-Time', (new Date().getTime() - time) / 1000 + ' s')
  })

  nitroApp.hooks.hook('afterResponse', (event, response) => {
    if (!isEnabled(event)) {
      return
    }

    const etag = getResponseHeader(event, 'etag')
    if (!etag && response?.body) {
      const key = getKey(event)
      storage.setItem(key, {
        time: new Date().getTime(),
        headers: getResponseHeaders(event),
        body: response.body,
        status: getStatusCode(event),
      })
    }
  })
})
