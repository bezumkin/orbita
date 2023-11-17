import {defineStore} from 'pinia'

const tokenName = 'auth:token'
const tokenType = 'Bearer'

export const useAuthStore = defineStore('auth', () => {
  // const maxAge = Number(useRuntimeConfig().public.JWT_EXPIRE)
  const maxAge = 14 * 86400 // @TODO temporary fix https://github.com/nuxt/nuxt/issues/24227
  const token = useCookie(tokenName, {path: '/', maxAge, sameSite: true})
  const user: Ref<VespUser | undefined> = ref()
  const loggedIn = computed(() => Boolean(user.value && user.value.id > 0))

  async function login(username: string, password: string) {
    const {token} = await usePost('security/login', {username, password})
    if (token) {
      await setToken(token)
    }
  }

  async function logout() {
    if (token.value) {
      await usePost('security/logout')
    }
    await setToken(undefined)
  }

  async function loadUser() {
    if (token.value) {
      const {user} = await useGet('user/profile')
      if (user) {
        setUser(user)
      }
    }
  }

  function setUser(data: VespUser | undefined = undefined) {
    user.value = data && data.id ? data : undefined
  }

  async function setToken(data: string | undefined = undefined) {
    if (data) {
      token.value = tokenType + ' ' + data
      await loadUser()
    } else {
      token.value = undefined
      setUser(undefined)
    }
  }

  return {loggedIn, token, user, loadUser, login, logout, setToken}
})
