import {defineStore} from 'pinia'

export const useVespStore = defineStore('settings', () => {
  const settings: Ref<Record<string, string | string[]>> = ref({})
  const isMobile = ref(false)
  const sidebar = ref(false)
  const login = ref(false)

  async function load() {
    try {
      const {rows} = await useApi('web/settings')
      rows.forEach((i: Record<string, string>) => {
        settings.value[i.key] = i.value
      })
    } catch (e) {
      console.error(e)
    }
  }

  return {settings, load, isMobile, sidebar, login}
})
