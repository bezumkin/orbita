declare global {
  type VespUserRole = {
    id: number
    title: string
    color?: string
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
    new_avatar?: {file: string; metadata: {[key: string]: any}} | boolean
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
    qualities?: VespVideoQuality[]
    [key: string]: any
  }

  type VespVideoQuality = {
    quality: string
    video_id: string
    file_id?: string
    progress?: string
    processed?: boolean
    moved?: boolean
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
    color?: string
    active: boolean
    new_cover?: {file: string; metadata: {[key: string]: any}} | boolean
    cover_id?: number
    cover?: VespFile
  }

  type VespTopic = {
    id: number
    uuid?: string
    title: string
    content: Record<string, any>
    user_id?: number
    level_id?: number
    teaser?: string
    type?: string
    price?: number | string
    active: boolean
    closed: boolean
    hide_comments?: boolean
    hide_views?: boolean
    hide_reactions?: boolean
    new_cover?: {file: string; metadata: {[key: string]: any}} | boolean
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
    active?: boolean
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
    active?: boolean
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

  type VespCategory = {
    id: number
    title?: string
    uri?: string
    active?: boolean
    [key: string]: any
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

export {}
