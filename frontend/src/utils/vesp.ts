import type {OutputData} from '@editorjs/editorjs'
import {ru, de} from 'date-fns/locale'
import {format} from 'date-fns'
import slugify from 'slugify'

export function getFileLink(file: VespFile | Record<string, any>, options?: VespFileOptions, prefix?: string): string {
  return getImageLink(file, options, prefix || 'file').replace(/([?&])fm=.*?&/, '$1')
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
    } else if (service === 'peertube') {
      url = 'https://peertube.tv/videos/embed/' + id
    }

    if (url && autoplay) {
      url += (url.includes('?') ? '&' : '?') + 'autoplay=1'
    }
  }
  return url
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

  let text = stripTags(blocks.join('\n\n'))
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
    const local = useNuxtApp().$variables.value.SITE_URL
    if (!/:/.test(href) || href.startsWith(local)) {
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
  const userSections = useNuxtApp().$variables.value.ADMIN_SECTIONS || ''
  const allSections: Record<string, any> = {
    payments: {scope: 'payments/get', title: 'payments', route: 'admin-payments'},
    topics: {scope: 'topics/get', title: 'topics', route: 'admin-topics'},
    levels: {scope: 'levels/get', title: 'levels', route: 'admin-levels'},
    settings: {scope: 'settings/get', title: 'settings', route: 'admin-settings'},
    videos: {scope: 'videos/get', title: 'videos', route: 'admin-videos'},
    notifications: {scope: 'notifications/get', title: 'notifications', route: 'admin-notifications'},
    pages: {scope: 'pages/get', title: 'pages', route: 'admin-pages'},
    users: {scope: 'users/get', title: 'users', route: 'admin-users'},
    roles: {scope: 'roles/get', title: 'user_roles', route: 'admin-user-roles'},
    redirects: {scope: 'redirects/get', title: 'redirects', route: 'admin-redirects'},
  }

  let sections: Record<string, any>[] = []
  if (userSections) {
    const allKeys = Object.keys(allSections)
    const userKeys = userSections.split(',').map((key: string) => key.toLowerCase().trim())
    userKeys.forEach((key: string) => {
      if (key === '-' || key === '') {
        sections.push({disabled: true})
      } else if (allKeys.includes(key)) {
        sections.push(allSections[key])
      }
    })
  } else {
    sections = Object.values(allSections)
  }

  return sections.filter((i) => !i.scope || hasScope(i.scope))
}

export function stripTags(str: string): string {
  return str.replace(/(<([^>]+)>)/g, '')
}

export function useDateLocale() {
  return computed(() => {
    const code = useI18n().locale.value
    if (code === 'ru') {
      return ru
    }
    if (code === 'de') {
      return de
    }
    return undefined
  })
}

export function formatBigNumber(views: undefined | number | string) {
  return views ? String(views).replace(/(\d)(?=(\d{3})+$)/g, '$1 ') : '0'
}

export function formatServiceKey(service: string) {
  return slugify(
    service.trim().replace(/[A-Z]/g, (s) => '-' + s),
    {lower: true},
  )
}

export function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}

export function handleBackBtn(topic: VespTopic | undefined = undefined) {
  const router = useRouter()
  if (window.history.length > 1) {
    router.back()
  } else {
    router.replace(topic?.category ? {name: 'topics', params: {topics: topic.category.uri}} : {name: 'index'})
  }
}

export function formatPrice(val: number, zero: boolean = false) {
  if (!val && !zero) {
    return ''
  }
  const {$i18n, $variables} = useNuxtApp()
  if ($i18n) {
    const locale = $i18n.locales.value.find((i: any) => i.code === $i18n.locale.value)
    if (locale && typeof locale !== 'string') {
      const formatter = new Intl.NumberFormat(locale.language || 'ru-RU', {
        currency: $variables.value.CURRENCY,
        style: 'currency',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
      })
      return formatter.format(val)
    }
  }

  return String(val)
}

export function formatDate(date: string | Date) {
  return date ? format(date, 'dd.MM.yyyy HH:mm:ss') : ''
}

export function formatDateShort(date: string | Date) {
  return date ? format(date, 'dd.MM.yyyy') : ''
}

export function getTopicTypes() {
  return ['video', 'image', 'text']
}
