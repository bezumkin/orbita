import type {Composer} from 'vue-i18n'
import {Socket} from 'socket.io-client'
import {storeToRefs} from 'pinia'
import {useVespStore} from '~/stores/vesp'

export default defineNuxtPlugin((nuxtApp) => {
  const $i18n = nuxtApp.$i18n as Composer
  const $socket = nuxtApp.$socket as Socket
  const store = useVespStore()
  const {sidebar, login, isMobile, payment, variables} = storeToRefs(store)

  if ($socket) {
    // Listen for Settings update
    $socket.on('setting', ({key, value}: {key: string; value: string}) => {
      try {
        store.settings[key] = JSON.parse(value)
      } catch (e) {
        store.settings[key] = value
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

    // Listen for common objects update
    const items = {category: 'categories', page: 'pages', level: 'levels', reaction: 'reactions'}
    Object.keys(items).forEach((key: string) => {
      // @ts-ignore
      const storeData: Record<string, any>[] = store[items[key]]
      $socket.on(key + '-create', (item: any) => {
        if (item.active) {
          storeData.push(item)
        }
      })
      $socket.on(key + '-update', (item: any) => {
        const idx = storeData.findIndex((i: any) => i.id === item.id)
        if (idx > -1) {
          if (!item.active) {
            storeData.splice(idx, 1)
          } else {
            storeData[idx] = item
          }
        } else if (item.active) {
          storeData.push(item)
        }
      })
    })
  }

  return {
    provide: {
      file: getFileLink,
      contentPreview,
      contentClick,
      sidebar,
      login,
      isMobile,
      payment,
      price: formatPrice,
      variables,
      settings: computed(() => {
        const settings: Record<string, any> = {}
        Object.keys(store.settings).forEach((key: string) => {
          let value: string | Record<string, any> = store.settings[key]
          if (value && typeof value === 'object' && value[$i18n.locale.value]) {
            value = value[$i18n.locale.value]
          }
          settings[key] = value
        })
        console.log(settings.description)
        return settings
      }),
      categories: computed(() => [...store.categories].sort((a, b) => (Number(a.rank) > Number(b.rank) ? 1 : -1))),
      pages: computed(() => [...store.pages].sort((a, b) => (Number(a.rank) > Number(b.rank) ? 1 : -1))),
      levels: computed(() => [...store.levels].sort((a, b) => (Number(a.price) > Number(b.price) ? 1 : -1))),
      reactions: computed(() => [...store.reactions].sort((a, b) => (Number(a.rank) > Number(b.rank) ? 1 : -1))),
    },
  }
})
