import {Socket} from 'socket.io-client'
import i18n from "@nuxtjs/i18n/dist/runtime/plugins/i18n.mjs";

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
    role_id?: number
    role?: VespUserRole
    avatar_id?: number
    avatar?: VespFile
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
}

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $i18n: i18n
    $image: getImageLink
    $scope: hasScope
    $socket: Socket
    $settings: Ref<Record<string, string | string[]>>
    $isMobile: Ref<boolean>
  }
}

declare module '#app' {
  interface NuxtApp {
    $i18n: i18n
    $image: getImageLink
    $scope: hasScope
    $socket: Socket
    $settings: Ref<Record<string, string | string[]>>
    $isMobile: Ref<boolean>
  }
}

export {}
