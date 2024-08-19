import type {H3Event} from 'h3'
import {format} from 'date-fns'
// @ts-ignore
import etag from 'etag'

import {cacheTime, getKey, isEnabled} from '../plugins/cache'

export default defineEventHandler(async (event: H3Event) => {
  if (!isEnabled(event)) {
    return
  }

  const key = getKey(event)
  const storage = useStorage('cache')
  const cache = await storage.getItem<{time: number; body: string; headers: Record<string, any>}>(key)
  const time = new Date().getTime()
  if (cache && time - cache.time < cacheTime * 1000) {
    setResponseHeaders(event, {
      ...cache.headers,
      'Cache-Control': `s-maxage=${cacheTime}, stale-while-revalidate`,
      'Last-Modified': format(new Date(cache.time), 'EEE, dd MMM yyyy HH:mm:ss zzz'),
      ETag: etag(cache.body),
      'X-From-Cache': 'true',
    })
    return cache.body
  }

  setResponseHeader(event, 'X-From-Cache', 'false')
})
