export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.provide('scope', hasScope)
  nuxtApp.provide('image', getImageLink)
})
