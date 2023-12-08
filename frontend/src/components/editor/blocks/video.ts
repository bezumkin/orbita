import type {BlockTool, BlockToolConstructorOptions, BlockToolData} from '@editorjs/editorjs'
import Hls from 'hls.js'
import sprite from 'assets/icons/plyr.svg'

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
      button.classList.add('plyr__control', 'plyr__control--overlaid')
      button.innerHTML = `<svg aria-hidden="true" focusable="false"><use xlink:href="${sprite}#plyr-play" /></svg>`
      button.onclick = (e) => {
        e.preventDefault()
        this.activated = true
        this.html.innerHTML = ''
        this.showPreview()
      }

      wrapper.classList.add('plyr', 'plyr--full-ui', 'plyr--video')
      wrapper.append(posterWrapper)
      wrapper.append(button)
    } else {
      const source = document.createElement('source')
      source.src = getApiUrl() + 'video/' + this.data.uuid
      source.type = 'application/x-mpegURL'

      const video = document.createElement('video')
      video.poster = getApiUrl() + 'poster/' + this.data.uuid + '/1024'
      video.appendChild(source)

      wrapper.appendChild(video)
      wrapper.classList.add('ratio', 'ratio-16x9', 'm-auto', 'rounded')

      const {$plyr} = useNuxtApp()
      const hls = new Hls()
      hls.loadSource(source.src)
      hls.attachMedia(video)

      // eslint-disable-next-line import/no-named-as-default-member
      hls.on(Hls.Events.MANIFEST_PARSED, () => {
        const levels = hls.levels.map((i: Record<string, any>) => i.height)
        const player = $plyr(video, {
          quality: {
            default: levels[0],
            options: levels,
            forced: true,
            onChange(size: number) {
              hls.currentLevel = levels.findIndex((i) => i === size)
            },
          },
        })
        player.play()
      })
    }

    this.html.appendChild(wrapper)
  }
}
