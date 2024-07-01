import {getRssFeed} from '~/utils/get-feed'

export default defineEventHandler(async (event) => {
  setHeader(event, 'content-type', 'application/rss+xml')
  setHeader(event, 'cache-control', 'max-age=3600')

  return (await getRssFeed()).rss2()
})
