import type {StoreGeneric} from 'pinia'
import {storeToRefs} from 'pinia'
import {useAuthStore} from '~/stores/auth'

export default (): AuthStore => {
  const store: StoreGeneric = useAuthStore()

  const {loggedIn, user, token} = storeToRefs(store)
  const {loadUser, login, logout, setToken} = store

  return {user, token, loggedIn, loadUser, login, logout, setToken}
}
