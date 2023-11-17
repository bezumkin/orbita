import type {FetchOptions, FetchContext} from 'ofetch'
import type {UseFetchOptions} from 'nuxt/app'
import {ofetch} from 'ofetch'

function onRequest({options}: FetchContext): void {
  const {token} = useAuth()
  if (token.value) {
    const headers = new Headers(options.headers || {})
    headers.set('Authorization', token.value)
    options.headers = headers
  }
}

function onResponseError({response}: FetchContext): void {
  if (process.client && response?._data) {
    const {t} = useNuxtApp().$i18n
    useToastError(t ? t(response._data) : response._data)
  }
}

export function useApi(endpoint: string, options: FetchOptions<any> = {}) {
  return ofetch(endpoint, {
    baseURL: getApiUrl(),
    onRequest,
    onResponseError,
    ...options,
  })
}

export function useCustomFetch(endpoint: string, options: UseFetchOptions<any> = {}) {
  return useFetch(endpoint, {
    baseURL: getApiUrl(),
    key: options.key || endpoint.split('/').join('-'),
    onRequest,
    onResponseError,
    ...options,
  })
}

export function useGet(endpoint: string, params = {}, options: FetchOptions<any> = {}) {
  return useApi(endpoint, {...options, query: params, method: 'GET'})
}

export function usePost(endpoint: string, params = {}, options: FetchOptions<any> = {}) {
  return useApi(endpoint, {...options, body: params, method: 'POST'})
}

export function usePut(endpoint: string, params = {}, options: FetchOptions<any> = {}) {
  return useApi(endpoint, {...options, body: params, method: 'PUT'})
}

export function usePatch(endpoint: string, params = {}, options: FetchOptions<any> = {}) {
  return useApi(endpoint, {...options, body: params, method: 'PATCH'})
}

export function useDelete(endpoint: string, params = {}, options: FetchOptions<any> = {}) {
  return useApi(endpoint, {...options, query: params, method: 'DELETE'})
}
