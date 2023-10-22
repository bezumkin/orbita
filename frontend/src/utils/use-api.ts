import {ofetch} from 'ofetch'
import type {FetchOptions} from 'ofetch'

export function useApi(endpoint: string | Ref, options: FetchOptions<any> = {}) {
  return ofetch(typeof endpoint === 'object' ? endpoint.value : endpoint, {
    baseURL: getApiUrl(),
    method: 'GET',
    // https://github.com/unjs/ofetch#%EF%B8%8F-interceptors
    ...options,
    onRequest({options}) {
      const {token} = useAuth()
      if (token.value) {
        const headers = new Headers(options.headers || {})
        headers.set('Authorization', token.value)
        options.headers = headers
      }
    },
    onResponseError({response}) {
      if (response._data) {
        const {t} = useNuxtApp().$i18n
        useToastError(t ? t(response._data) : response._data)
      }
    },
  })
}

export async function useGet(endpoint: string | Ref, params = {}, options: FetchOptions<any> = {}) {
  return await useApi(endpoint, {...options, query: params, method: 'GET'})
}

export async function usePost(endpoint: string | Ref, params = {}, options: FetchOptions<any> = {}) {
  return await useApi(endpoint, {...options, body: params, method: 'POST'})
}

export async function usePut(endpoint: string | Ref, params = {}, options: FetchOptions<any> = {}) {
  return await useApi(endpoint, {...options, body: params, method: 'PUT'})
}

export async function usePatch(endpoint: string | Ref, params = {}, options: FetchOptions<any> = {}) {
  return await useApi(endpoint, {...options, body: params, method: 'PATCH'})
}

export async function useDelete(endpoint: string | Ref, params = {}, options: FetchOptions<any> = {}) {
  return await useApi(endpoint, {...options, query: params, method: 'DELETE'})
}
