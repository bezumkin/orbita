import {defineStore} from 'pinia'

export const useVespStore = defineStore('settings', () => {
  const settings: Ref<Record<string, string | string[]>> = ref({})
  const pages: Ref<Record<string, any>[]> = ref([])
  const isMobile = ref(false)
  const sidebar = ref(false)
  const login = ref(false)

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

  return {settings, pages, isMobile, sidebar, login}
})
