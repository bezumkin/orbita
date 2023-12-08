import type {OutputData} from '@editorjs/editorjs'

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

export function getImageLink(file: VespFile | Record<string, any>, options?: VespFileOptions, prefix?: string): string {
  const params = [getApiUrl().slice(0, -1), prefix || 'image', file.uuid]
  if (file.updated_at) {
    if (!options) {
      options = {}
    }
    if (!options.fm) {
      options.fm = 'webp'
    }
    options.t = file.updated_at.split('.').shift()?.replaceAll(/\D/g, '')
  }
  return params.join('/') + '?' + new URLSearchParams(options as Record<string, string>).toString()
}

export function getFileLink(file: VespFile | Record<string, any>, options?: VespFileOptions, prefix?: string): string {
  return getImageLink(file, options, prefix || 'file')
}

export function getEmbedLink(service?: string, id?: string, autoplay: boolean = true) {
  let url
  if (service && id) {
    if (service === 'youtube') {
      url = 'https://www.youtube.com/embed/' + id
    } else if (service === 'vimeo') {
      url = 'https://player.vimeo.com/video/' + id
    } else if (service === 'rutube') {
      url = 'https://rutube.ru/play/embed/' + id
    } else if (service === 'vk') {
      const parts = id.split('_')
      url = 'https://vk.com/video_ext.php?oid=' + parts[0] + '&id=' + parts[1]
    }

    if (url && autoplay) {
      url += (url.includes('?') ? '&' : '?') + 'autoplay=1'
    }
  }
  return url
}

export function hasScope(scopes: string | string[]): boolean {
  const {user: data} = useAuth()
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

export function scrollPageTo(id: string, offset: number = 65) {
  const el = document.getElementById(id)
  if (el) {
    window.scrollTo({
      top: el.getBoundingClientRect().top + window.scrollY - offset,
      behavior: 'smooth',
    })
  }
}

export function setLocationHash(hash?: string) {
  const href = document.location.href
  if (!hash) {
    history.replaceState({}, '', href.replace(/#.*/, ''))
  } else if (href.includes('#')) {
    history.replaceState({}, '', href.replace(/#.*/, '#' + hash))
  } else {
    history.replaceState({}, '', href + '#' + hash)
  }
}

export function contentPreview(content: OutputData, length: number = 100) {
  if (!content.blocks || !Array.isArray(content.blocks)) {
    return ''
  }
  const blocks: string[] = []
  content.blocks?.forEach((i) => {
    if (i.type === 'paragraph' && i.data.text) {
      blocks.push(i.data.text)
    }
  })

  let text = blocks.join('\n\n').replace(/<\/?[^>]+(>|$)/g, '')
  const entities = [
    ['amp', '&'],
    ['apos', "'"],
    ['#x27', "'"],
    ['#x2F', '/'],
    ['#39', "'"],
    ['#47', '/'],
    ['lt', '<'],
    ['gt', '>'],
    ['nbsp', ' '],
    ['quot', '"'],
  ]
  entities.forEach((i: string[]) => {
    text = text.replace(new RegExp('&' + i[0] + ';', 'g'), i[1])
  })

  return text.length > length + 3 ? text.slice(0, length) + '...' : text
}

export function contentClick(e: MouseEvent) {
  let target = e.target as HTMLElement

  // Handle links
  if (['I', 'B'].includes(target.tagName)) {
    let parent = target.parentElement
    while (parent) {
      if (parent.tagName === 'A') {
        target = parent
        break
      }
      parent = parent.parentElement
    }
  }
  if (target.tagName === 'A') {
    e.preventDefault()
    const href = (target as HTMLLinkElement).href
    const local = useRuntimeConfig().public.SITE_URL
    if (!/:\/\//.test(href) || href.startsWith(local)) {
      const router = useRouter()
      const route = router.resolve(href.replace(local, '/'))
      if (route) {
        router.push(route)
      }
    } else {
      window.open(href)
    }
  }
}

export function translateServerMessage(message: string) {
  const check = message.match(/^You have no "(.*?)" scope for this action$/)
  if (check) {
    const {$i18n} = useNuxtApp()
    if ($i18n) {
      return $i18n.t('errors.scope', {scope: check[1]})
    }
  }
  return message
}

export function getAdminSections() {
  const items = [
    {scope: 'payments/get', title: 'payments', route: 'admin-payments'},
    {scope: 'topics/get', title: 'topics', route: 'admin-topics'},
    {scope: 'levels/get', title: 'levels', route: 'admin-levels'},
    {scope: 'settings/get', title: 'settings', route: 'admin-settings'},
    {scope: 'videos/get', title: 'videos', route: 'admin-videos'},
    {scope: 'notifications/get', title: 'notifications', route: 'admin-notifications'},
    {scope: 'pages/get', title: 'pages', route: 'admin-pages'},
    {scope: 'users/get', title: 'users', route: 'admin-users'},
    {scope: 'roles/get', title: 'user_roles', route: 'admin-user-roles'},
  ]

  return items.filter((i) => hasScope(i.scope))
}
