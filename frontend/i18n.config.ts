import type {DateTimeFormat} from '@intlify/core-base'

const dateFormat: DateTimeFormat = {
  short: {
    year: 'numeric',
    month: 'numeric',
    day: 'numeric',
  },
  long: {
    year: 'numeric',
    month: 'numeric',
    day: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    second: 'numeric',
    hour12: false,
  },
}

export default defineI18nConfig(() => ({
  legacy: false,
  locale: 'ru',
  pluralRules: {
    ru: (choice, choicesLength) => {
      if (choice === 0) {
        return 0
      }

      const teen = choice > 10 && choice < 20
      const endsWithOne = choice % 10 === 1
      if (!teen && endsWithOne) {
        return 1
      }
      if (!teen && choice % 10 >= 2 && choice % 10 <= 4) {
        return 2
      }

      return choicesLength < 4 ? 2 : 3
    },
  },
  datetimeFormats: {
    ru: dateFormat,
    en: dateFormat,
  },
}))
