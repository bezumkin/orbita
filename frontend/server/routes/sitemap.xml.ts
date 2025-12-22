import {ofetch} from 'ofetch'
import {js2xml} from 'xml-js'

export default defineEventHandler(async (event) => {
  setHeader(event, 'content-type', 'application/xml')
  setHeader(event, 'cache-control', 'max-age=3600')

  const base: any = {
    _declaration: {
      _attributes: {version: '1.0', encoding: 'utf-8'},
    },
    urlset: {
      _attributes: {
        'xmlns:xsi': 'http://www.w3.org/2001/XMLSchema-instance',
        'xmlns:video': 'http://www.google.com/schemas/sitemap-video/1.1',
        'xmlns:xhtml': 'http://www.w3.org/1999/xhtml',
        'xmlns:image': 'http://www.google.com/schemas/sitemap-image/1.1',
        'xmlns:news': 'http://www.google.com/schemas/sitemap-news/0.9',
        'xsi:schemaLocation': `http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd`,
        xmlns: 'http://www.sitemaps.org/schemas/sitemap/0.9',
      },
      url: [],
    },
  }

  try {
    const rows = await ofetch(getApiUrl() + 'web/sitemap')
    rows.forEach((row: any) => {
      base.urlset.url.push(row)
    })
  } catch (e) {
    console.error(e)
  }

  return js2xml(base, {compact: true, ignoreComment: true, spaces: 4})
})
