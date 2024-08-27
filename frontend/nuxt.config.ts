// https://nuxt.com/docs/api/configuration/nuxt-config

import type {NuxtConfig} from '@nuxt/schema'

const enabledLocales = (process.env.LOCALES || 'ru,en,de').split(',')
const locales = [
  {code: 'ru', name: 'Русский', file: 'lexicons/ru.js', iso: 'ru-RU'},
  {code: 'en', name: 'English', file: 'lexicons/en.js', iso: 'en-GB'},
  {code: 'de', name: 'Deutsch', file: 'lexicons/de.js', iso: 'de-DE'},
].filter((i) => enabledLocales.includes(i.code))
const SITE_URL = process.env.SITE_URL || 'http://127.0.0.1:8080/'

const config: NuxtConfig = {
  telemetry: false,
  srcDir: 'src/',
  css: ['~/assets/scss/index.scss'],
  devtools: {enabled: false},
  vite: {
    server: {
      hmr: {port: 3001},
    },
    css: {
      preprocessorOptions: {
        scss: {
          additionalData: '@use "@/assets/scss/_variables.scss" as *;',
          quietDeps: true,
        },
      },
    },
  },
  nitro: {
    experimental: {websocket: true},
    storage: {cache: {driver: 'redis', host: 'redis'}},
    devStorage: {cache: {driver: 'redis', host: 'redis'}},
  },
  routeRules: {
    '/admin/**': {ssr: false},
    '/user/**': {ssr: false},
    '/search': {ssr: false},
  },
  runtimeConfig: {
    CACHE_PAGES_TIME: process.env.CACHE_PAGES_TIME,
    SOCKET_SECRET: process.env.SOCKET_SECRET,
    YANDEX_METRIKA_ID: process.env.YANDEX_METRIKA_ID,
    locales,
    public: {
      TZ: process.env.TZ || 'Europe/Moscow',
      SITE_URL,
      API_URL: process.env.API_URL || '/api/',
      JWT_EXPIRE: process.env.JWT_EXPIRE || '2592000',
      CURRENCY: process.env.CURRENCY || 'RUB',
      REGISTER_ENABLED: process.env.REGISTER_ENABLED || '1',
      COMMENTS_SHOW_ONLINE: process.env.COMMENTS_SHOW_ONLINE || '1',
      COMMENTS_MAX_LEVEL: process.env.COMMENTS_MAX_LEVEL || '3',
      COMMENTS_EDIT_TIME: process.env.COMMENTS_EDIT_TIME || '600',
      EDITOR_TOPIC_BLOCKS: process.env.EDITOR_TOPIC_BLOCKS || '',
      EDITOR_COMMENT_BLOCKS: process.env.EDITOR_COMMENT_BLOCKS || '',
      PAYMENT_SERVICES: process.env.PAYMENT_SERVICES || '',
      PAYMENT_SUBSCRIPTIONS: process.env.PAYMENT_SUBSCRIPTIONS || '',
      CONNECTION_SERVICES: process.env.CONNECTION_SERVICES || '',
      ADMIN_SECTIONS: process.env.ADMIN_SECTIONS || '',
      DOWNLOAD_MEDIA_ENABLED: process.env.DOWNLOAD_MEDIA_ENABLED || '0',
      EXTRACT_VIDEO_AUDIO_ENABLED: process.env.EXTRACT_VIDEO_AUDIO_ENABLED || '0',
      EXTRACT_VIDEO_THUMBNAILS_ENABLED: process.env.EXTRACT_VIDEO_THUMBNAILS_ENABLED || '1',
      COMMENTS_REQUIRE_SUBSCRIPTION: process.env.COMMENTS_REQUIRE_SUBSCRIPTION || '0',
      TOPICS_SHOW_AUTHOR: process.env.TOPICS_SHOW_AUTHOR || '0',
      TOPICS_CHANGE_AUTHOR: process.env.TOPICS_CHANGE_AUTHOR || '0',
    },
  },
  app: {
    pageTransition: {name: 'page', mode: 'out-in'},
    layoutTransition: {name: 'page', mode: 'out-in'},
    head: {
      title: process.env.SITE_NAME,
      viewport: 'width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=0',
      meta: [{name: 'msapplication-config', content: '/favicons/browserconfig.xml'}],
      link: [
        {rel: 'apple-touch-icon', sizes: '180x180', href: '/favicons/apple-touch-icon.png'},
        {rel: 'icon', sizes: '32x32', href: '/favicons/favicon-32x32.png', type: 'image/png'},
        {rel: 'icon', sizes: '16x16', href: '/favicons/favicon-16x16.png', type: 'image/png'},
        {rel: 'manifest', href: '/favicons/site.webmanifest'},
        {rel: 'mask-icon', href: '/favicons/safari-pinned-tab.svg'},
        {rel: 'shortcut icon', href: '/favicons/favicon.ico'},
        {rel: 'alternate', type: 'application/rss+xml', title: 'RSS', href: '/rss.xml'},
        {rel: 'alternate', type: 'application/atom+xml', title: 'Atom', href: '/atom.xml'},
      ],
    },
  },
  modules: ['@vesp/frontend'],
  vesp: {
    icons: {
      solid: [
        'user',
        'power-off',
        'globe',
        'filter',
        'pause',
        'play',
        'upload',
        'question',
        'image',
        'video',
        'file',
        'music',
        'code',
        'calendar',
        'cloud-arrow-down',
        'comment',
        'comments',
        'bars',
        'right-to-bracket',
        'hashtag',
        'reply',
        'trash',
        'undo',
        'paper-plane',
        'wallet',
        'hourglass-half',
        'lock',
        'lock-open',
        'heading',
        'list',
        'face-smile',
        'tags',
        'external-link',
        'magnifying-glass',
        'arrow-up-wide-short',
        'arrow-down-short-wide',
        'download',
      ],
      regular: ['face-smile', 'sun', 'moon'],
    },
  },
  i18n: {
    defaultLocale: locales[0].code,
    detectBrowserLanguage: {
      fallbackLocale: locales[0].code,
    },
    locales,
  },
}

if (process.env.NODE_ENV === 'development') {
  config.modules?.push('@nuxtjs/eslint-module', '@nuxtjs/stylelint-module')
  // @ts-ignore
  config.eslint = {
    lintOnStart: false,
  }
  // @ts-ignore
  config.stylelint = {
    lintOnStart: false,
  }
}

if (process.env.YANDEX_METRIKA_ID && Number(process.env.YANDEX_METRIKA_ID) > 0) {
  let options = {}
  if (process.env.YANDEX_METRIKA_OPTIONS) {
    try {
      options = JSON.parse(process.env.YANDEX_METRIKA_OPTIONS)
    } catch (e) {}
  }
  config.modules?.push(['yandex-metrika-module-nuxt3', {...options, id: process.env.YANDEX_METRIKA_ID}])
}

export default defineNuxtConfig(config)
