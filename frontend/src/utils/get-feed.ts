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
