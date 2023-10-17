import { Socket } from 'socket.io-client'

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
    new_avatar?: {file: string, metadata: {[key: string]: any}} | Boolean
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
}

declare module 'vue' {
  interface ComponentCustomProperties {
    $image: typeof getImageLink
    $scope: typeof hasScope
    $socket: typeof Socket
  }
}

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $image: typeof getImageLink
    $scope: typeof hasScope
    $socket: typeof Socket
  }
}

export {}