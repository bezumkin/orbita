import {Feed} from 'feed'
import {ofetch} from 'ofetch'
import {stripTags} from '~/utils/vesp'

const {public: config, locales} = useRuntimeConfig()
const SITE_URL = String(config.SITE_URL)
// @ts-ignore
const locale = locales.length > 0 ? locales[0].code : 'en'
const baseURL = getApiUrl()

export async function getRssFeed() {
  const feed = new Feed({
    title: '',
    description: '',
    id: SITE_URL,
    link: SITE_URL,
    image: '',
    copyright: '',
    generator: 'Orbita',
    feedLinks: {
      rss: SITE_URL + 'rss.xml',
      atom: SITE_URL + 'atom.xml',
    },
  })

  try {
    const [{rows: settings}, topics] = await Promise.all([
      ofetch('web/settings', {baseURL}),
      ofetch('web/rss', {baseURL}),
    ])
    topics.forEach((topic: any) => {
      feed.addItem({...topic, date: new Date(topic.date)})
    })

    settings.forEach((setting: any) => {
      if (['title', 'description', 'copyright'].includes(setting.key) && setting.value[locale]) {
        // @ts-ignore
        feed.options[setting.key] = stripTags(setting.value[locale])
      } else if (setting.key === 'poster' && setting.value.uuid) {
        feed.options.image =
          baseURL + 'image/' + setting.value.uuid + '?t=' + new Date(setting.value.updated_at).getTime()
      }
    })
  } catch (e) {
    console.error(e)
  }

  return feed
}

function getApiUrl(): string {
  const {public: config} = useRuntimeConfig()
  const SITE_URL = String(config.SITE_URL || 'http://localhost')
  const API_URL = String(config.API_URL || 'api')
  const url = /:\/\//.test(API_URL)
    ? API_URL
    : [
        SITE_URL.endsWith('/') ? SITE_URL.slice(0, -1) : SITE_URL,
        API_URL.startsWith('/') ? API_URL.substring(1) : API_URL,
      ].join('/')
  return url.endsWith('/') ? url : url + '/'
}
