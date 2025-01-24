import {defineStore} from 'pinia'

export const useVespStore = defineStore('settings', () => {
  const settings = ref<Record<string, string | string[]>>({})
  const variables = ref<Record<string, string>>({})
  const categories = ref<VespCategory[]>([])
  const pages = ref<VespPage[]>([])
  const levels = ref<VespLevel[]>([])
  const reactions = ref<VespReaction[]>([])
  const isMobile = ref(false)
  const sidebar = ref(false)
  const login = ref(false)
  const payment = ref<undefined | VespTopic | VespLevel>()

  useCustomFetch('web/settings', {
    onResponse({response}) {
      settings.value = response._data?.settings || {}
      variables.value = response._data?.variables || {}
      categories.value = response._data?.categories || []
      pages.value = response._data?.pages || []
      levels.value = response._data?.levels || []
      reactions.value = response._data?.reactions || []
    },
  })

  /*
  useCustomFetch('web/categories', {
    onResponse({response}) {
      categories.value = []
      response._data?.rows?.forEach((category: VespCategory) => {
        categories.value.push(category)
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

  useCustomFetch('web/reactions', {
    onResponse({response}) {
      reactions.value = []
      response._data?.rows?.forEach((reaction: VespReaction) => {
        reactions.value.push(reaction)
      })
    },
  }) */

  return {settings, variables, categories, pages, levels, reactions, isMobile, sidebar, payment, login}
})
