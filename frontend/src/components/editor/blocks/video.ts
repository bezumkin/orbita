import type {BlockTool, BlockToolConstructorOptions, BlockToolData, ToolConfig} from '@editorjs/editorjs'
import {icon} from '@fortawesome/fontawesome-svg-core'
import {faPlay, faVideo} from '@fortawesome/free-solid-svg-icons'
import {initAudioPlayer} from '~/utils/players'

export default class implements BlockTool {
  data: BlockToolData
  html: HTMLElement
  config?: ToolConfig
  activated: boolean
  playing?: string
  time?: number

  static get toolbox() {
    return {
      title: 'Video',
      icon: icon(faVideo).html[0],
    }
  }

  constructor({data, config}: BlockToolConstructorOptions) {
    this.data = data
    this.config = config
    this.html = document.createElement('div')
    this.activated = false
  }

  render() {
    if (this.data && Object.keys(this.data).length) {
      this.showPreview()
    } else if (this.config.click) {
      this.config.click()
    }
    return this.html
  }

  save() {
    return this.data && this.data.uuid ? this.data : null
  }

  static get isReadOnlySupported() {
    return true
  }

  showPreview() {
    if (!this.data.uuid) {
      return
    }

    const {$i18n, $variables} = useNuxtApp()
    const t = $i18n.t
    this.html.innerHTML = ''
    const videoWrapper = document.createElement('div')

    if (!this.playing) {
      const poster = document.createElement('img')
      poster.src = getApiUrl() + 'poster/' + this.data.uuid + '/1024'

      const posterWrapper = document.createElement('div')
      posterWrapper.classList.add('ratio', 'ratio-16x9', 'm-auto', 'rounded')
      posterWrapper.append(poster)

      const button = document.createElement('button')
      button.classList.add('btn', 'btn-primary', 'play-video-button')
      button.innerHTML = icon(faPlay).html[0]
      button.onclick = (e) => {
        e.preventDefault()
        this.playing = 'video'
        this.showPreview()
      }
      const fa = button.firstChild as HTMLElement
      fa.style.width = '3rem'
      fa.style.height = '3rem'
      fa.classList.add('fa-fw')

      videoWrapper.classList.add('play-video-wrapper')
      videoWrapper.append(posterWrapper)
      videoWrapper.append(button)
    } else if (this.playing === 'video') {
      const video = document.createElement('video')
      videoWrapper.appendChild(video)
      videoWrapper.classList.add('ratio', 'ratio-16x9', 'm-auto', 'rounded')

      initVideoPlayer(this.data.uuid, video, {
        poster: getApiUrl() + 'poster/' + this.data.uuid + '/1024',
        autoPlay: true,
        status: {
          time: this.time,
        },
      })
    } else {
      const button = document.createElement('button')
      button.classList.add('btn', 'btn-primary')
      button.textContent = t('models.video.play.video')
      button.onclick = (e) => {
        e.preventDefault()
        if (this.playing === 'audio') {
          const storage = JSON.parse(localStorage.getItem('player-audio') || {})
          if (this.data.audio in storage) {
            this.time = storage[this.data.audio].time || 0
          }
        }
        this.playing = 'video'
        this.showPreview()
      }
      videoWrapper.classList.add('text-center')
      videoWrapper.appendChild(button)
    }

    this.html.appendChild(videoWrapper)

    const useAudio = Number($variables.value.EXTRACT_VIDEO_AUDIO_ENABLED) && this.data.audio
    if (useAudio) {
      const audioWrapper = document.createElement('div')
      audioWrapper.classList.add('mt-2')
      if (this.playing === 'audio') {
        const audio = document.createElement('audio')
        audio.title = t('models.video.audio')
        audioWrapper.appendChild(audio)

        initAudioPlayer(this.data.audio, audio, {
          autoPlay: true,
          status: {
            time: this.time,
          },
        })
      } else {
        const button = document.createElement('button')
        button.classList.add('btn', 'btn-primary')
        button.textContent = t('models.video.play.audio')
        button.onclick = (e) => {
          e.preventDefault()
          if (this.playing === 'video') {
            const storage = JSON.parse(localStorage.getItem('player-video') || {})
            if (this.data.uuid in storage) {
              this.time = storage[this.data.uuid].time || 0
            }
          }
          this.playing = 'audio'
          this.showPreview()
        }
        audioWrapper.classList.add('text-center')
        audioWrapper.appendChild(button)
      }

      this.html.appendChild(audioWrapper)
    }
  }
}
