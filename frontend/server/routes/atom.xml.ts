import {getRssFeed} from '~/utils/get-feed'

export default defineEventHandler(async (event) => {
  setHeader(event, 'content-type', 'application/atom+xml')
  setHeader(event, 'cache-control', 'max-age=3600')

  return (await getRssFeed()).atom1()
})
