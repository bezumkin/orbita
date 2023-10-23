export default defineNuxtPlugin((nuxtApp) => {
  const auth = useAuth()

  // Load user profile
  nuxtApp.hook('app:created', async () => {
    if (auth.token.value) {
      try {
        await auth.loadUser()
      } catch (e: any) {
        if (e.response && e.response.status === 401) {
          auth.setToken(undefined)
        }
      }
    }
  })

  return {
    provide: {auth},
  }
})
