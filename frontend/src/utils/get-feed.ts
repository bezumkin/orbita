import {Feed} from 'feed'
import {ofetch} from 'ofetch'
import {stripTags} from '~/utils/vesp'

export async function getRssFeed() {
  const {public: config, locales} = useRuntimeConfig()
  const SITE_URL = String(config.SITE_URL)
  // @ts-ignore
  const locale = locales.length > 0 ? locales[0].code : 'en'
  const baseURL = getApiUrl()

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
    const [{settings}, topics] = await Promise.all([ofetch('web/settings', {baseURL}), ofetch('web/rss', {baseURL})])
    topics.forEach((topic: any) => {
      feed.addItem({...topic, date: new Date(topic.date)})
    })

    Object.keys(settings).forEach((key: any) => {
      const value = settings[key]
      if (['title', 'description', 'copyright'].includes(key) && value[locale]) {
        // @ts-ignore
        feed.options[key] = stripTags(value[locale])
      } else if (key === 'poster' && value.uuid) {
        feed.options.image = baseURL + 'image/' + value.uuid + '?t=' + new Date(value.updated_at).getTime()
      }
    })
  } catch (e) {
    console.error(e)
  }

  return feed
}
