import type {BlockTool, BlockToolConstructorOptions, BlockToolData, ToolConfig} from '@editorjs/editorjs'
import {icon} from '@fortawesome/fontawesome-svg-core'
import {faPlay, faVideo} from '@fortawesome/free-solid-svg-icons'
import {initAudioPlayer} from '~/utils/players'

export default class implements BlockTool {
  data: BlockToolData
  html: HTMLElement
  config?: ToolConfig
  activated: boolean

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

    const wrapper = document.createElement('div')

    if (!this.activated) {
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
        this.activated = true
        this.html.innerHTML = ''
        this.showPreview()
      }
      const fa = button.firstChild as HTMLElement
      fa.style.width = '3rem'
      fa.style.height = '3rem'
      fa.classList.add('fa-fw')

      wrapper.classList.add('play-video-wrapper')
      wrapper.append(posterWrapper)
      wrapper.append(button)
    } else {
      const video = document.createElement('video')
      wrapper.appendChild(video)
      wrapper.classList.add('ratio', 'ratio-16x9', 'm-auto', 'rounded')

      initVideoPlayer(this.data.uuid, video, {
        poster: getApiUrl() + 'poster/' + this.data.uuid + '/1024',
        autoPlay: true,
      })
    }

    this.html.appendChild(wrapper)

    const useAudio = Number(useRuntimeConfig().public.EXTRACT_VIDEO_AUDIO_ENABLED)
    if (useAudio && this.data.audio) {
      wrapper.classList.add('mb-2')

      const audio = document.createElement('audio')
      audio.title = useNuxtApp().$i18n.t('models.video.audio')
      this.html.appendChild(audio)

      initAudioPlayer(this.data.audio, audio)
    }
  }
}
