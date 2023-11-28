import {defineStore} from 'pinia'

export const useVespStore = defineStore('settings', () => {
  const settings: Ref<Record<string, string | string[]>> = ref({})
  const pages: Ref<VespPage[]> = ref([])
  const levels: Ref<VespLevel[]> = ref([])
  const isMobile = ref(false)
  const sidebar = ref(false)
  const login = ref(false)
  const payment: Ref<undefined | VespTopic | VespLevel> = ref()

  useCustomFetch('web/settings', {
    onResponse({response}) {
      settings.value = {}
      response._data?.rows?.forEach((i: VespSetting) => {
        settings.value[i.key] = i.value
      })
    },
  })

  useCustomFetch('web/pages', {
    onResponse({response}) {
      pages.value = []
      response._data?.rows?.forEach((page: VespPage) => {
        pages.value.push(page)
      })
    },
  })

  useCustomFetch('web/levels', {
    onResponse({response}) {
      levels.value = []
      response._data?.rows?.forEach((page: VespLevel) => {
        levels.value.push(page)
      })
    },
  })

  return {settings, pages, levels, isMobile, sidebar, payment, login}
})
