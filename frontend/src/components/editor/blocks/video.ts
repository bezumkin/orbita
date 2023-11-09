import type {BlockTool, BlockToolConstructorOptions, BlockToolData} from '@editorjs/editorjs'
import Hls from 'hls.js'

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
    return this.data && this.data.uuid ? this.data : null
  }

  static get isReadOnlySupported() {
    return true
  }

  showPreview() {
    if (!this.data.uuid) {
      return
    }

    const source = document.createElement('source')
    source.src = getApiUrl() + 'video/' + this.data.uuid
    source.type = 'application/x-mpegURL'

    const video = document.createElement('video')
    video.classList.add('mw-100')
    video.poster = getApiUrl() + 'poster/' + this.data.uuid + '/1024'
    video.appendChild(source)

    this.html.appendChild(video)

    const {$plyr} = useNuxtApp()
    const hls = new Hls()
    hls.loadSource(source.src)
    hls.attachMedia(video)

    // eslint-disable-next-line import/no-named-as-default-member
    hls.on(Hls.Events.MANIFEST_PARSED, () => {
      const levels = hls.levels.map((i: Record<string, any>) => i.height)
      $plyr(video, {
        quality: {
          default: levels[0],
          options: levels,
          forced: true,
          onChange(size: number) {
            hls.currentLevel = levels.findIndex((i) => i === size)
          },
        },
      })
    })
  }
}
