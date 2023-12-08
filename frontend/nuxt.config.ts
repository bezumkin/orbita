// https://nuxt.com/docs/api/configuration/nuxt-config

const enabledLocales = (process.env.LOCALES || 'ru,en,de').split(',')
const locales = [
  {code: 'ru', name: 'Русский', file: 'ru.ts', iso: 'ru-RU'},
  {code: 'en', name: 'English', file: 'en.ts', iso: 'en-US'},
  {code: 'de', name: 'Deutsch', file: 'de.ts', iso: 'de-DE'},
].filter((i) => enabledLocales.includes(i.code))

export default defineNuxtConfig({
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
  routeRules: {
    '/admin/**': {ssr: false},
    '/user/**': {ssr: false},
  },
  router: {
    options: {
      // scrollBehaviorType: undefined,
    },
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
    },
  },
  modules: [
    '@nuxtjs/eslint-module',
    '@nuxtjs/stylelint-module',
    '@pinia/nuxt',
    '@bootstrap-vue-next/nuxt',
    '@nuxtjs/i18n',
  ],
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
  experimental: {
    inlineSSRStyles: false,
  },
  eslint: {
    lintOnStart: false,
  },
  stylelint: {
    lintOnStart: false,
  },
  i18n: {
    strategy: 'no_prefix',
    defaultLocale: locales[0].code,
    langDir: 'lexicons',
    locales,
  },
})
