import {defineStore} from 'pinia'

const tokenName = 'auth:token'
const tokenType = 'Bearer'

export const useAuthStore = defineStore('auth', () => {
  const maxAge = Number(useRuntimeConfig().public.JWT_EXPIRE)
  const token = useCookie(tokenName, {maxAge})
  const user: Ref<VespUser | undefined> = ref()
  const loggedIn = computed(() => Boolean(user.value && user.value.id > 0))

  async function login(username: string, password: string) {
    const {token} = await usePost('security/login', {username, password})
    if (token) {
      await setToken(token)
    }
  }

  async function logout() {
    await usePost('security/logout')
    setToken(undefined)
  }

  async function loadUser() {
    const {user} = await useGet('user/profile')
    setUser(user)

    return user
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
