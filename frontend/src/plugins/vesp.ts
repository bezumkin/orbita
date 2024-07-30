import type {Composer} from 'vue-i18n'
import {Socket} from 'socket.io-client'
import {storeToRefs} from 'pinia'
import {useVespStore} from '~/stores/vesp'

export default defineNuxtPlugin((nuxtApp) => {
  const $i18n = nuxtApp.$i18n as Composer
  const $socket = nuxtApp.$socket as Socket
  const currency = (nuxtApp.$config.public.CURRENCY || 'RUB') as string
  const store = useVespStore()
  const {sidebar, login, isMobile, payment} = storeToRefs(store)

  nuxtApp.provide('file', getFileLink)
  nuxtApp.provide('contentPreview', contentPreview)
  nuxtApp.provide('contentClick', contentClick)
  nuxtApp.provide('sidebar', sidebar)
  nuxtApp.provide('login', login)
  nuxtApp.provide('isMobile', isMobile)
  nuxtApp.provide('payment', payment)
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
    // @ts-ignore
    computed(() => store.pages.sort((a, b) => (a.rank > b.rank ? 1 : -1))),
  )

  nuxtApp.provide(
    'levels',
    computed(() => store.levels.sort((a, b) => (a.price > b.price ? 1 : -1))),
  )

  nuxtApp.provide(
    'reactions',
    // @ts-ignore
    computed(() => store.reactions.sort((a, b) => (a.rank > b.rank ? 1 : -1))),
  )

  if ($socket) {
    // Listen for Settings update
    $socket.on('setting', ({key, value}: {key: string; value: string | string[]}) => {
      store.settings[key] = value
    })

    // Listen for Pages update
    $socket.on('page-create', (page: VespPage) => {
      if (page.active) {
        store.pages.push(page)
      }
    })
    $socket.on('page-update', (page: VespPage) => {
      const idx = store.pages.findIndex((i: VespPage) => i.id === page.id)
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

    // Listen for Levels update
    $socket.on('level-create', (level: VespLevel) => {
      if (level.active) {
        store.levels.push(level)
      }
    })
    $socket.on('level-update', (level: VespLevel) => {
      const idx = store.levels.findIndex((i: VespLevel) => i.id === level.id)
      if (idx > -1) {
        if (!level.active) {
          store.levels.splice(idx, 1)
        } else {
          store.levels[idx] = level
        }
      } else if (level.active) {
        store.levels.push(level)
      }
    })

    // Listen for Reactions update
    $socket.on('reaction-create', (reaction: VespReaction) => {
      if (reaction.active) {
        store.reactions.push(reaction)
      }
    })
    $socket.on('reaction-update', (reaction: VespReaction) => {
      const idx = store.reactions.findIndex((i: VespReaction) => i.id === reaction.id)
      if (idx > -1) {
        if (!reaction.active) {
          store.reactions.splice(idx, 1)
        } else {
          store.reactions[idx] = reaction
        }
      } else if (reaction.active) {
        store.reactions.push(reaction)
      }
    })
    $socket.on('reactions', () => {
      refreshNuxtData('web-reactions')
    })

    // Listen for user profile update
    $socket.on('profile', ({id}: VespUser) => {
      const {user, loadUser} = useAuth()
      if (user.value?.id === id) {
        loadUser()
      }
    })
  }
})
