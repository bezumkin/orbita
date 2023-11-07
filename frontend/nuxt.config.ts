// https://nuxt.com/docs/api/configuration/nuxt-config

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
  },
  runtimeConfig: {
    REDIS_SECRET: process.env.REDIS_SECRET,
    public: {
      SITE_URL: process.env.SITE_URL || '127.0.0.1',
      API_URL: process.env.API_URL || '/api/',
      JWT_EXPIRE: process.env.JWT_EXPIRE || '2592000',
      CURRENCY: process.env.CURRENCY || 'RUB',
      REGISTER_ENABLED: process.env.REGISTER_ENABLED || '1',
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
  eslint: {
    lintOnStart: false,
  },
  stylelint: {
    lintOnStart: false,
  },
  i18n: {
    strategy: 'no_prefix',
    defaultLocale: 'ru',
    langDir: 'lexicons',
    locales: [
      {code: 'ru', name: 'Русский', file: 'ru.ts', iso: 'ru-RU'},
      {code: 'en', name: 'English', file: 'en.ts', iso: 'en-US'},
    ],
  },
})
