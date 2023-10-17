export function getApiUrl(): string {
  const {public: config} = useRuntimeConfig()
  const SITE_URL = String(config.SITE_URL)
  const API_URL = String(config.API_URL)
  const url = /:\/\//.test(API_URL)
    ? API_URL
    : [
        SITE_URL.endsWith('/') ? SITE_URL.slice(0, -1) : SITE_URL,
        API_URL.startsWith('/') ? API_URL.substring(1) : API_URL,
      ].join('/')
  return url.endsWith('/') ? url : url + '/'
}

export function getImageLink(file: VespFile, options?: VespFileOptions, prefix?: string): string {
  const params = [getApiUrl().slice(0, -1), prefix || 'image', file.uuid]
  if (file.updated_at) {
    if (!options) {
      options = {}
    }
    options.t = file.updated_at.split('.').shift()?.replaceAll(/\D/g, '')
  }
  return params.join('/') + '?' + new URLSearchParams(options as Record<string, string>).toString()
}

export function hasScope(scopes: string | string[]): boolean {
  const {data} = useAuth()
  const user: VespUser = {id: 0, username: '', ...data.value}
  if (!user || !user.role || !user.role.scope) {
    return false
  }
  const {role} = user

  const check = (scope: string) => {
    if (scope.includes('/')) {
      return role.scope.includes(scope) || role.scope.includes(scope.replace(/\/.*/, ''))
    }
    return role.scope.includes(scope) || role.scope.includes(`${scope}/get`)
  }

  if (Array.isArray(scopes)) {
    for (const scope of scopes) {
      if (check(scope)) {
        return true
      }
    }
    return false
  }

  return check(scopes)
}
