import {defineStore} from 'pinia'

export const useSettingsStore = defineStore('settings', () => {
  const settings: Ref<Record<string, string | string[]>> = ref({})

  async function load() {
    try {
      const {rows} = await useGet('web/settings')
      rows.forEach((i: Record<string, string>) => {
        settings.value[i.key] = i.value
      })
    } catch (e) {
      // console.error(e)
    }
  }

  return {settings, load}
})
