import type {BlockTool, BlockToolConstructorOptions, BlockToolData} from '@editorjs/editorjs'
import Plyr from 'plyr'
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
    video.poster = getApiUrl() + 'poster/' + this.data.uuid
    video.appendChild(source)

    this.html.appendChild(video)

    const hls = new Hls()
    hls.loadSource(source.src)
    hls.attachMedia(video)

    // eslint-disable-next-line import/no-named-as-default-member
    hls.on(Hls.Events.MANIFEST_PARSED, () => {
      const levels = hls.levels.map((i: Record<string, any>) => i.height)
      new Plyr(video, {
        storage: {
          enabled: false,
        },
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
