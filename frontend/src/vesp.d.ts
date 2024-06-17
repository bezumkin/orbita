import type {Options} from 'plyr'
import Plyr from 'plyr'
import {Socket} from 'socket.io-client'
import i18n from '@nuxtjs/i18n/dist/runtime/plugins/i18n.mjs'
import {getImageLink, hasScope} from '@vesp/frontend'
import {getFileLink} from '~/utils/vesp'

declare global {
  type VespUserRole = {
    id: number
    title: string
    scope: string[]
  }

  type VespUser = {
    id: number
    username: string
    fullname?: string
    password?: string
    email?: string
    phone?: string
    active?: boolean
    blocked?: boolean
    notify?: boolean
    role_id?: number
    role?: VespUserRole
    avatar_id?: number
    avatar?: VespFile
    subscription?: VespSubscription
    new_avatar?: {file: string; metadata: {[key: string]: any}} | Boolean
  }

  type VespFile = {
    id?: number
    uuid: string
    updated_at?: string
    [key: string]: any
  }

  type VespVideo = {
    id: string
    title?: string
    description?: string
    [key: string]: any
  }

  type VespFileOptions = {
    w?: string | number
    h?: string | number
    fit?: string
    fm?: string
    t?: string | number
  }

  type VespSetting = {
    key: string
    value: string
    type: string
    required: boolean
  }

  type VespLevel = {
    id: number
    title: string
    content?: string
    price: number
    active: bool
    new_cover?: {file: string; metadata: {[key: string]: any}} | Boolean
    cover_id?: number
    cover?: VespFile
  }

  type VespTopic = {
    id: number
    uuid?: string
    title: string
    content: Record<string, any>
    user_id?: VespUser
    level_id?: number
    teaser?: string
    price?: number | string
    active: bool
    closed: bool
    new_cover?: {file: string; metadata: {[key: string]: any}} | Boolean
    cover_id?: number
    cover?: VespFile
    level?: VespLevel
    viewed_at?: string
    comments_count?: number
    views_count?: number
    unseen_comments_count?: number
    reactions_count?: number
    tags?: VespTag[]
    reaction?: number
    [key: string]: any
  }

  type VespComment = {
    id: number
    topic_id?: number
    topic?: VespTopic
    content: Record<string, any>
    active?: bool
    created_at?: string
    user?: VespUser
    children?: VespComment[]
    reactions_count?: number
    reaction?: number
    [key: string]: any
  }

  type VespPage = {
    id: number
    title: string
    content: Record<string, any>
    alias?: string
    position?: string
    rank?: number
    active?: bool
    [key: string]: any
  }

  type VespSubscription = {
    id?: number
    user_id?: number
    level_id: number
    next_level_id?: number
    service?: string
    period?: number
    cancelled?: boolean
    active?: boolean
    active_until?: string
    [key: string]: any
  }

  type VespPayment = {
    id: number
    amount: number
    topic?: VespTopic
    subscription?: VespSubscription
    metadata?: Record<string, any>
  }

  type VespTag = {
    id: number
    title: string
    topics?: number
    [key: string]: any
  }

  type VespReaction = {
    id: number
    title: string
    emoji: string
    rank?: number
    active?: boolean
  }

  type VespRedirect = {
    id: number
    title?: string
    from: string
    to: string
    code?: number
    message?: string
    active?: boolean
  }
}

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $i18n: i18n
    $image: getImageLink
    $file: getFileLink
    $scope: hasScope
    $price: Function
    $prism: Function
    $prismLanguages: Record<string, any>
    $socket: Socket
    $sidebar: Ref<boolean>
    $login: Ref<boolean>
    $settings: Ref<Record<string, string | string[] | Record<string, any>>>
    $pages: Ref<VespPage[]>
    $levels: Ref<VespLevel[]>
    $reactions: Ref<VespReaction[]>
    $payment: Ref<undefined | VespTopic | VespLevel>
    $isMobile: Ref<boolean>
    $plyr: (element: HTMLElement | string, options: Options = {}) => Plyr
    $plyrOptions: Options
    $contentPreview: Function
    $contentClick: (e: MouseClick) => void
  }
}

declare module '#app' {
  interface NuxtApp {
    $i18n: i18n
    $image: getImageLink
    $file: getFileLink
    $scope: hasScope
    $price: Function
    $prism: Function
    $prismLanguages: Record<string, any>
    $socket: Socket
    $sidebar: Ref<boolean>
    $login: Ref<boolean>
    $settings: Ref<Record<string, string | string[] | Record<string, any>>>
    $pages: Ref<Record<string, any>[]>
    $levels: Ref<VespLevel[]>
    $reactions: Ref<VespReaction[]>
    $payment: Ref<undefined | VespTopic | VespLevel>
    $isMobile: Ref<boolean>
    $plyr: (element: HTMLElement | string, options: Options = {}) => Plyr
    $plyrOptions: Options
    $contentPreview: Function
    $contentClick: (e: MouseClick) => void
  }
}

export {}
