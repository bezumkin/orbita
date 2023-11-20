import type {Composer} from 'vue-i18n'
import {Socket} from 'socket.io-client'
import {storeToRefs} from 'pinia'
import {useVespStore} from '~/stores/vesp'

export default defineNuxtPlugin((nuxtApp) => {
  const $i18n = nuxtApp.$i18n as Composer
  const $socket = nuxtApp.$socket as Socket
  const currency = (nuxtApp.$config.public.CURRENCY || 'RUB') as string
  const store = useVespStore()
  const {sidebar, login, isMobile} = storeToRefs(store)

  nuxtApp.provide('scope', hasScope)
  nuxtApp.provide('image', getImageLink)
  nuxtApp.provide('file', getFileLink)
  nuxtApp.provide('contentPreview', contentPreview)
  nuxtApp.provide('contentClick', contentClick)
  nuxtApp.provide('sidebar', sidebar)
  nuxtApp.provide('login', login)
  nuxtApp.provide('isMobile', isMobile)
  nuxtApp.provide('price', (val: number) => {
    if (!val) {
      return ''
    }
    const locale = $i18n.locales.value.find((i: any) => i.code === $i18n.locale.value)
    if (locale && typeof locale !== 'string') {
      const formatter = new Intl.NumberFormat(locale.iso || 'ru-RU', {
        currency,
        style: 'currency',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
      })
      return formatter.format(val)
    }
    return val
  })
  nuxtApp.provide(
    'settings',
    computed(() => {
      const settings: Record<string, any> = {}
      Object.keys(store.settings).forEach((key: string) => {
        let value: string | Record<string, any> = store.settings[key]
        if (value && typeof value === 'object' && value[$i18n.locale.value]) {
          value = value[$i18n.locale.value]
        }
        settings[key] = value
      })
      return settings
    }),
  )
  nuxtApp.provide(
    'pages',
    computed(() => store.pages.sort((a, b) => (a.rank > b.rank ? 1 : -1))),
  )

  // Listen for settings update
  if ($socket) {
    $socket.on('setting', ({key, value}: {key: string; value: string | string[]}) => {
      store.settings[key] = value
    })
    $socket.on('page-create', (page: VespPage) => {
      if (page.active) {
        store.pages.push(page)
      }
    })
    $socket.on('page-update', (page: VespPage) => {
      const idx = store.pages.findIndex((i: any) => i.id === page.id)
      console.log(idx)
      if (idx > -1) {
        if (!page.active) {
          store.pages.splice(idx, 1)
        } else {
          store.pages[idx] = page
        }
      } else if (page.active) {
        store.pages.push(page)
      }
    })
  }
})
