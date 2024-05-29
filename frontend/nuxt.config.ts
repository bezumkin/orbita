// https://nuxt.com/docs/api/configuration/nuxt-config

import type {NuxtConfig} from '@nuxt/schema'

const enabledLocales = (process.env.LOCALES || 'ru,en,de').split(',')
const locales = [
  {code: 'ru', name: 'Русский', file: 'ru.js', iso: 'ru-RU'},
  {code: 'en', name: 'English', file: 'en.js', iso: 'en-GB'},
  {code: 'de', name: 'Deutsch', file: 'de.js', iso: 'de-DE'},
].filter((i) => enabledLocales.includes(i.code))

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
        scss: {additionalData: '@use "@/assets/scss/_variables.scss" as *;'},
      },
    },
  },
  nitro: {
    experimental: {
      websocket: true,
    },
  },
  routeRules: {
    '/admin/**': {ssr: false},
    '/user/**': {ssr: false},
  },
  runtimeConfig: {
    SOCKET_SECRET: process.env.SOCKET_SECRET,
    public: {
      TZ: process.env.TZ || 'Europe/Moscow',
      SITE_URL: process.env.SITE_URL || '127.0.0.1',
      API_URL: process.env.API_URL || '/api/',
      JWT_EXPIRE: process.env.JWT_EXPIRE || '2592000',
      CURRENCY: process.env.CURRENCY || 'RUB',
      REGISTER_ENABLED: process.env.REGISTER_ENABLED || '1',
      COMMENTS_SHOW_ONLINE: process.env.COMMENTS_SHOW_ONLINE || '3',
      COMMENTS_MAX_LEVEL: process.env.COMMENTS_MAX_LEVEL || '3',
      COMMENTS_EDIT_TIME: process.env.COMMENTS_EDIT_TIME || '600',
      EDITOR_TOPIC_BLOCKS: process.env.EDITOR_TOPIC_BLOCKS || '',
      EDITOR_COMMENT_BLOCKS: process.env.EDITOR_COMMENT_BLOCKS || '',
      PAYMENT_SERVICES: process.env.PAYMENT_SERVICES || '',
      PAYMENT_SUBSCRIPTIONS: process.env.PAYMENT_SUBSCRIPTIONS || '',
      ADMIN_SECTIONS: process.env.ADMIN_SECTIONS || '',
    },
  },
  app: {
    pageTransition: {name: 'page', mode: 'out-in'},
    layoutTransition: {name: 'page', mode: 'out-in'},
    head: {
      title: process.env.SITE_NAME,
      meta: [
        {name: 'msapplication-TileColor', content: '#ffffff'},
        {name: 'msapplication-config', content: '/favicons/browserconfig.xml'},
        {name: 'theme-color', content: '#ffffff'},
      ],
      link: [
        {rel: 'apple-touch-icon', sizes: '180x180', href: '/favicons/apple-touch-icon.png'},
        {rel: 'icon', sizes: '32x32', href: '/favicons/favicon-32x32.png', type: 'image/png'},
        {rel: 'icon', sizes: '16x16', href: '/favicons/favicon-16x16.png', type: 'image/png'},
        {rel: 'manifest', href: '/favicons/site.webmanifest', color: '#fff'},
        {rel: 'mask-icon', href: '/favicons/safari-pinned-tab.svg'},
        {rel: 'shortcut icon', href: '/favicons/favicon.ico'},
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
        'circle-half-stroke',
      ],
      regular: ['face-smile', 'sun', 'moon'],
    },
  },
  i18n: {
    defaultLocale: locales[0].code,
    langDir: 'lexicons',
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

export default defineNuxtConfig(config)
