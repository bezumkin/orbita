import {Socket} from 'socket.io-client'
import type {Composer} from 'vue-i18n'
import {useVespStore} from '~/stores/vesp'

export default defineNuxtPlugin(async (nuxtApp) => {
  const $i18n = nuxtApp.$i18n as Composer
  const $socket = nuxtApp.$socket as Socket
  const store = useVespStore()
  await store.load()

  nuxtApp.provide('scope', hasScope)
  nuxtApp.provide('image', getImageLink)
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
  nuxtApp.provide('isMobile', ref(store.isMobile))

  // Listen for settings update
  if ($socket) {
    $socket.on('setting', ({key, value}: {key: string; value: string | string[]}) => {
      store.settings[key] = value
    })
  }
})
