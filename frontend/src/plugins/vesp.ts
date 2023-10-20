import {Socket} from 'socket.io-client'
import {Composer} from 'vue-i18n'
import {useSettingsStore} from '~/stores/settings'

export default defineNuxtPlugin(async (nuxtApp) => {
  const $i18n = nuxtApp.$i18n as Composer
  const $socket = nuxtApp.$socket as Socket
  const {settings, load} = useSettingsStore()
  await load()

  nuxtApp.provide('scope', hasScope)
  nuxtApp.provide('image', getImageLink)
  nuxtApp.provide('settings', settings)
  nuxtApp.provide('setting', (key: string): string | Record<string, any> => {
    let value: string | Record<string, any> = settings[key]
    if (value && typeof value === 'object' && value[$i18n.locale.value]) {
      value = value[$i18n.locale.value]
    }
    return value || ''
  })

  // Listen for settings update

  if ($socket) {
    $socket.on('setting', ({key, value}: {key: string; value: string | string[]}) => {
      settings[key] = value
    })
  }
})
