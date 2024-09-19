import {
  defineCustomElement,
  MediaAudioLayoutElement,
  MediaPlayerElement,
  MediaProviderElement,
  MediaVideoLayoutElement,
} from 'vidstack/elements'
import {VidstackPlayer, VidstackPlayerLayout} from 'vidstack/global/player'
import {
  type DefaultLayoutProps,
  type MediaErrorEvent,
  type MediaPlayerProps,
  type MediaStorage,
  type SerializedVideoQuality,
  TextTrack,
} from 'vidstack'
import ruLexicon from '~/lexicons/ru-player'
import {useToastError} from '#build/imports'

defineCustomElement(MediaPlayerElement)
defineCustomElement(MediaProviderElement)
defineCustomElement(MediaVideoLayoutElement)
defineCustomElement(MediaAudioLayoutElement)

const commonSettings: Partial<MediaPlayerProps> = {
  controls: false,
  streamType: 'on-demand',
  logLevel: 'warn',
  playsInline: true,
}
const layoutSettings: Partial<DefaultLayoutProps> = {
  playbackRates: {min: 0.25, max: 3, step: 0.25},
  colorScheme: 'default',
  flatSettingsMenu: true,
  noAudioGain: true,
  noAudioTracks: true,
  noCaptions: true,
  noAnnouncements: true,
  noKeyboardAnimations: true,
  noCaptionStyles: true,
  noMediaLoop: true,
}

function onError(e: MediaErrorEvent) {
  useToastError(e.detail.message || 'Unknown message')
}

export async function initAudioPlayer(uuid: string, target: HTMLElement, props: Record<string, any> = {}) {
  const url = getApiUrl() + 'audio/' + uuid
  try {
    const {$i18n} = useNuxtApp()
    if ($i18n.locale.value === 'ru') {
      layoutSettings.translations = ruLexicon
    }
  } catch (e) {}

  const player = await VidstackPlayer.create({
    target,
    src: {src: url, type: 'audio/mpeg'},
    viewType: 'audio',
    storage: new MediaDatabaseStorage({uuid, type: 'audio', status: props.status}),
    ...commonSettings,
    layout: new VidstackPlayerLayout({
      ...layoutSettings,
    }),
    ...props,
  })

  player.addEventListener('error', onError)

  return player
}

export async function initVideoPlayer(uuid: string, target: HTMLElement, props: Record<string, any> = {}) {
  const url = getApiUrl() + 'video/' + uuid
  try {
    const {$i18n, $variables} = useNuxtApp()
    if ($i18n.locale.value === 'ru') {
      layoutSettings.translations = ruLexicon
    }
    const {EXTRACT_VIDEO_THUMBNAILS_ENABLED} = $variables.value
    if (Number(EXTRACT_VIDEO_THUMBNAILS_ENABLED)) {
      const data = await useGet(url + '/thumbnails')
      if (data.file) {
        data.url = getImageLink(data.file)
        layoutSettings.thumbnails = data
      }
    }
  } catch (e) {}

  const player = await VidstackPlayer.create({
    target,
    src: {src: url, type: 'application/x-mpegurl'},
    viewType: 'video',
    storage: new MediaDatabaseStorage({uuid, type: 'video', status: props.status}),
    ...commonSettings,
    layout: new VidstackPlayerLayout({
      ...layoutSettings,
    }),
    ...props,
  })

  player.addEventListener('error', onError)

  player.addEventListener('can-play', async () => {
    if (props.quality) {
      for (const quality of player.qualities) {
        if (quality.height === props.quality) {
          quality.selected = true
        }
      }
    } else {
      player.remoteControl.requestAutoQuality()
    }

    if (props.currentTime) {
      player.remoteControl.seek(props.currentTime)
    }

    if (props.playbackRate) {
      player.remoteControl.changePlaybackRate(props.playbackRate)
    }

    if (props.volume) {
      player.remoteControl.changeVolume(props.volume)
    }

    const chapters = await loadVideoChapters(url + '/chapters', player.duration)
    player.textTracks.add(chapters as TextTrack)
  })

  return player
}

async function loadVideoChapters(url: string, duration: number) {
  try {
    const data = await useGet(url)
    const timestamps: Record<string, any>[] = []
    Object.keys(data).forEach((time: string) => {
      timestamps.push({
        text: data ? data[time] : '',
        // @ts-ignore
        time: time.split(':').reduce((a, b) => Number(a) * 60 + Number(b)),
      })
    })

    return {
      type: 'json',
      kind: 'chapters',
      default: true,
      content: {
        cues: timestamps.map((item: any, idx, arr) => {
          return {startTime: item.time, endTime: arr[idx + 1]?.time || duration, text: item.text}
        }),
      },
    }
  } catch (e) {}
}

class MediaDatabaseStorage implements MediaStorage {
  timeout: any = null // Timeout instance
  delay: number = 5 // seconds
  loading: boolean = false
  uuid: string
  type: string
  user: VespUser | undefined
  status: Record<string, {quality: number | null; time: number; speed: number; volume: number}> = {}
  forcedStatus: Record<string, {quality: number | null; time: number; speed: number; volume: number}> = {}

  constructor({uuid, type, status}: Record<string, any>) {
    this.uuid = uuid
    this.type = type
    this.user = useAuth().user.value
    this.forcedStatus = status || {}
  }

  get isLocal() {
    return this.type === 'audio' || !this.user
  }

  setValue(key: 'quality' | 'time' | 'speed' | 'volume', value: any) {
    if (!this.status[this.uuid]) {
      this.status[this.uuid] = {quality: null, time: 0, speed: 1, volume: 1}
    }
    if (key === 'time') {
      if (value > this.status[this.uuid].time + this.delay || value < this.status[this.uuid].time - this.delay) {
        this.status[this.uuid].time = value
        this.onSave()
      }
    } else {
      this.status[this.uuid][key] = value
      this.onSave()
    }
  }

  getValue(key: 'quality' | 'time' | 'speed' | 'volume'): string | number | null {
    if (!this.status[this.uuid]) {
      return null
    }
    return Number(this.status[this.uuid][key])
  }

  async getVideoQuality() {
    return await new Promise<SerializedVideoQuality | null>((resolve) => {
      const quality = this.getValue('quality')
      if (quality) {
        resolve({id: '0', height: Number(quality), width: Number(quality) * 1.78})
      } else {
        resolve(null)
      }
    })
  }

  async setVideoQuality(quality: SerializedVideoQuality | null) {
    if (quality) {
      await new Promise<number>((resolve) => {
        this.setValue('quality', quality.height)
        this.onSave()
        resolve(quality.height)
      })
    }
  }

  async getAudioGain() {
    return await new Promise<null>((resolve) => {
      resolve(null)
    })
  }

  async getVolume() {
    return await new Promise<number>((resolve) => {
      resolve(this.getValue('volume') as number)
    })
  }

  async setVolume(volume: number) {
    await new Promise<number>((resolve) => {
      this.setValue('volume', volume)
      this.onSave()
      resolve(volume)
    })
  }

  async getMuted() {
    return (await this.getVolume()) === 0
  }

  async setMuted(isMuted: boolean) {
    const volume = await this.getVolume()
    if (!isMuted && volume === 0) {
      await this.setVolume(1)
    } else if (isMuted && volume > 0) {
      await this.setVolume(0)
    }
  }

  async getTime() {
    return await new Promise<number>((resolve) => {
      resolve(this.getValue('time') as number)
    })
  }

  async setTime(time: number) {
    await new Promise<number>((resolve) => {
      this.setValue('time', time)
      resolve(time)
    })
  }

  async getLang() {
    return await new Promise<null>((resolve) => {
      resolve(null)
    })
  }

  async setLang(_: string | null) {
    await new Promise<null>((resolve) => {
      resolve(null)
    })
  }

  async getCaptions() {
    return await new Promise<null>((resolve) => {
      resolve(null)
    })
  }

  async setCaptions(_: boolean) {
    await new Promise<null>((resolve) => {
      resolve(null)
    })
  }

  async getPlaybackRate() {
    return await new Promise<number>((resolve) => {
      resolve(this.getValue('speed') as number)
    })
  }

  async setPlaybackRate(rate: number) {
    await new Promise<number>((resolve) => {
      this.setValue('speed', rate)
      this.onSave()
      resolve(rate)
    })
  }

  async onSave() {
    if (this.timeout) {
      clearTimeout(this.timeout)
    }

    this.timeout = setTimeout(async () => {
      localStorage.setItem('player-' + this.type, JSON.stringify(this.status))
      if (!this.isLocal && !this.loading) {
        this.loading = true
        try {
          await usePost('user/video/' + this.uuid, this.status[this.uuid])
        } catch (e) {
        } finally {
          this.loading = false
        }
      }
    }, 500)

    await new Promise<null>((resolve) => {
      resolve(null)
    })
  }

  async onLoad() {
    try {
      const status = localStorage.getItem('player-' + this.type)
      if (status) {
        this.status = JSON.parse(status)
      }
      if (!this.isLocal) {
        const data = await useGet('user/video/' + this.uuid)
        if (typeof data === 'object' && 'quality' in data) {
          this.status[this.uuid] = data
        }
      }

      if (this.forcedStatus) {
        Object.keys(this.forcedStatus).forEach((key) => {
          this.setValue(key, this.forcedStatus[key])
        })
      }
    } catch (e) {
      console.error(e)
    }
  }
}
