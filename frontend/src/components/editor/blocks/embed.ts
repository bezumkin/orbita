import type {BlockTool, BlockToolConstructorOptions, BlockToolData} from '@editorjs/editorjs'
import {icon} from '@fortawesome/fontawesome-svg-core'
import {faPlay} from '@fortawesome/free-solid-svg-icons'

export default class implements BlockTool {
  data: BlockToolData
  html: HTMLElement
  activated: boolean

  constructor({data}: BlockToolConstructorOptions) {
    this.data = data
    this.html = document.createElement('div')
    this.activated = false
  }

  render() {
    this.showPreview()
    return this.html
  }

  save() {
    return this.data.url ? this.data : null
  }

  static get isReadOnlySupported() {
    return true
  }

  showPreview() {
    if (!this.data.url) {
      return
    }

    const wrapper = document.createElement('div')

    if (!this.activated && this.data.service && this.data.id) {
      const poster = document.createElement('img')
      poster.src = getApiUrl() + 'poster/embed/' + this.data.service + '/' + this.data.id

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
      const frame = document.createElement('iframe')
      frame.allowFullscreen = true
      frame.width = '1280'
      frame.height = '720'
      frame.src = getEmbedLink(this.data.service, this.data.id, this.activated) || this.data.url
      frame.classList.add('rounded')

      wrapper.classList.add('ratio', 'ratio-16x9', 'm-auto', 'rounded')
      wrapper.appendChild(frame)
    }

    this.html.appendChild(wrapper)
  }

  static get pasteConfig() {
    return {
      patterns: {
        vimeo: /https?:\/\/(?:player\.)?vimeo\.com\/(?:video\/)?(\d+).*/,
        rutube: /https?:\/\/rutube\.ru\/(?:video|play\/embed)\/(\w+).*/,
        vk: /https?:\/\/vk\.com\/video(?:\?z=video)?(-?\d+_\d+).*/,
        peertube: /https?:\/\/peertube\.tv\/(?:w|videos\/embed)\/([\w-]+).*/,
        youtube:
          /(?:https?:\/\/)?(?:www\.)?(?:(youtu\.be\/)|(youtube\.com)\/(?:v\/|u\/\w\/|embed\/|watch))(?:(?:\?v=)?([^#&?=]*))?((?:[?&]\w*=\w*)*)/,
      },
    }
  }

  onPaste(event: CustomEvent) {
    if (event.type === 'pattern') {
      // @ts-ignore
      const regex = this.constructor.pasteConfig.patterns[event.detail.key]
      const matches = event.detail.data.match(regex)
      if (matches) {
        this.data.service = event.detail.key
        this.data.url = event.detail.data
        if (event.detail.key === 'youtube') {
          this.data.id = matches[3]
        } else {
          this.data.id = matches[1]
        }
        this.showPreview()
      }
    }
  }
}
