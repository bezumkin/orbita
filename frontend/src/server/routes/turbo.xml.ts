import type {Item} from 'feed'
import {js2xml} from 'xml-js'
import {getRssFeed} from '~/utils/get-feed'

export default defineEventHandler(async (event) => {
  setHeader(event, 'content-type', 'application/rss+xml')
  setHeader(event, 'cache-control', 'max-age=3600')
  const feed = await getRssFeed()

  const base: any = {
    _declaration: {
      _attributes: {version: '1.0', encoding: 'utf-8'},
    },
    rss: {
      _attributes: {
        version: '2.0',
        'xmlns:yandex': 'http://news.yandex.ru',
        'xmlns:media': 'http://search.yahoo.com/mrss/',
        'xmlns:turbo': 'http://turbo.yandex.ru',
      },
      channel: {
        title: feed.options.title,
        link: sanitize(feed.options.link),
        description: feed.options.description,
        copyright: feed.options.copyright,
        item: [],
      },
    },
  }

  const {YANDEX_METRIKA_ID} = useRuntimeConfig()
  if (Number(YANDEX_METRIKA_ID) > 0) {
    base.rss.channel['turbo:analytics'] = {
      _attributes: {type: 'Yandex', id: YANDEX_METRIKA_ID},
    }
  }

  feed.items.forEach((entry: Item) => {
    const item: any = {
      _attributes: {turbo: true},
      link: sanitize(entry.link),
      'turbo:topic': {_cdata: entry.title},
      'turbo:content': {_cdata: `<header><h1>${entry.title}</h1></header>` + entry.content},
    }
    if (entry.date) {
      item.pubDate = entry.date.toUTCString()
    }
    base.rss.channel.item.push(item)
  })

  return js2xml(base, {compact: true, ignoreComment: true, spaces: 4})
})

function sanitize(url: string | undefined): string | undefined {
  if (typeof url === 'undefined') {
    return
  }
  return url.replace(/&/g, '&amp;')
}
