import type {Feed} from 'nuxt-module-feed'
import {ofetch} from 'ofetch'
import type {FeedOptions} from 'feed'
import {stripTags} from '~/utils/vesp'

const {public: config, locales} = useRuntimeConfig()
const SITE_URL = String(config.SITE_URL || 'http://localhost')
// @ts-ignore
const locale = locales.length > 0 ? locales[0].code : 'en'

function getApiUrl(): string {
  const API_URL = String(config.API_URL || 'api')
  const url = /:\/\//.test(API_URL)
    ? API_URL
    : [
        SITE_URL.endsWith('/') ? SITE_URL.slice(0, -1) : SITE_URL,
        API_URL.startsWith('/') ? API_URL.substring(1) : API_URL,
      ].join('/')
  return url.endsWith('/') ? url : url + '/'
}

export default defineNitroPlugin((nitroApp) => {
  nitroApp.hooks.hook('feed:generate', async ({feed}) => {
    await createRss(feed)
  })

  async function createRss(feed: Feed) {
    const options: FeedOptions = {
      id: '',
      link: SITE_URL,
      title: '',
      description: '',
      copyright: '',
    }
    try {
      const [{rows: settings}, topics] = await Promise.all([
        ofetch('web/settings', {baseURL: getApiUrl()}),
        ofetch('web/rss', {baseURL: getApiUrl()}),
      ])
      topics.forEach((topic: any) => {
        feed.addItem({...topic, date: new Date(topic.date)})
      })

      settings.forEach((setting: any) => {
        if (['title', 'description', 'copyright'].includes(setting.key) && setting.value[locale]) {
          // @ts-ignore
          options[setting.key] = stripTags(setting.value[locale])
        }
      })
    } catch (e) {
      console.error(e)
    }

    feed.options = options
  }
})
