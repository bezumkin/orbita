import type {BlockTool, BlockToolConstructorOptions, BlockToolData, ToolConfig} from '@editorjs/editorjs'
import Hls from 'hls.js'
import sprite from 'assets/icons/plyr.svg'

export default class implements BlockTool {
  data: BlockToolData
  html: HTMLElement
  config?: ToolConfig
  activated: boolean

  static get toolbox() {
    return {
      title: 'Video',
      icon: '<svg class="svg-inline--fa fa-fw" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M0 128C0 92.7 28.7 64 64 64H320c35.3 0 64 28.7 64 64V384c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V128zM559.1 99.8c10.4 5.6 16.9 16.4 16.9 28.2V384c0 11.8-6.5 22.6-16.9 28.2s-23 5-32.9-1.6l-96-64L416 337.1V320 192 174.9l14.2-9.5 96-64c9.8-6.5 22.4-7.2 32.9-1.6z"></path></svg>',
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
