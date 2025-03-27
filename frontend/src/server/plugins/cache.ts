import {createHash} from 'node:crypto'
import {defineNitroPlugin} from 'nitropack/runtime'
import {type H3Event} from 'h3'

export const cacheTime = Number(useRuntimeConfig().CACHE_PAGES_TIME) || 0
export const cookieName = 'auth:token'

export function getKey(event: H3Event): string {
  const url = URL.parse(event.path, 'https://localhost')
  if (!url) {
    return ''
  }

  let path = url.pathname.substring(1) || 'index'
  if (/^__nuxt_error/.test(path)) {
    // Handle Nuxt error page
    const tmp = url.searchParams.get('url')
    if (tmp) {
      path = tmp.split('?')[0].substring(1)
    }
  } else if (url.searchParams.size) {
    // Handle url params
    url.searchParams.sort()
    const tmp = JSON.stringify(Object.fromEntries(url.searchParams.entries()))
    path += '-' + createHash('sha1').update(tmp).digest('hex')
  }

  return 'routes:' + path.split('/').join(':')
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
