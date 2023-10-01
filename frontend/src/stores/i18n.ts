import {defineStore} from 'pinia'

export const useI18nStore = defineStore('i18n', {
  state: () => ({
    lang: 0,
  }),
})
