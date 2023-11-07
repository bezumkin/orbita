import type {BlockTool, BlockToolConstructorOptions, BlockToolData} from '@editorjs/editorjs'
import Plyr from 'plyr'

export default class implements BlockTool {
  data: BlockToolData
  html: HTMLElement

  constructor({data}: BlockToolConstructorOptions) {
    this.data = data
    this.html = document.createElement('div')
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
    const frame = document.createElement('iframe')
    frame.src = this.data.url
    frame.allowFullscreen = true
    frame.allow = 'autoplay'
    this.html.appendChild(frame)

    new Plyr(this.html)
  }

  static get pasteConfig() {
    return {
      patterns: {
        vimeo: /(?:https?:\/\/)?(?:www.)?(?:player.)?vimeo\.co(.+\/([^/]\d+)(?:#t=\d+)?s?$)/,
        twitch: /https?:\/\/www\.twitch\.tv\/(?:[^/?&]*\/v|videos)\/([0-9]*)/,
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
        this.data.url = event.detail.data
        this.showPreview()
      }
    }
  }
}
