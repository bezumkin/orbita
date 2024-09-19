import prettyBytes from 'pretty-bytes'
import {icon} from '@fortawesome/fontawesome-svg-core'
import {faDownload} from '@fortawesome/free-solid-svg-icons'
import Parent from './file'
import {initAudioPlayer} from '~/utils/players'

export default class extends Parent {
  type: string = 'audio'

  static get toolbox() {
    return {
      title: 'Audio',
      icon: '<svg class="svg-inline--fa fa-fw" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M499.1 6.3c8.1 6 12.9 15.6 12.9 25.7v72V368c0 44.2-43 80-96 80s-96-35.8-96-80s43-80 96-80c11.2 0 22 1.6 32 4.6V147L192 223.8V432c0 44.2-43 80-96 80s-96-35.8-96-80s43-80 96-80c11.2 0 22 1.6 32 4.6V200 128c0-14.1 9.3-26.6 22.8-30.7l320-96c9.7-2.9 20.2-1.1 28.3 5z"></path></svg>',
    }
  }

  static get pasteConfig() {
    return {
      files: {
        mimeTypes: ['audio/*'],
      },
    }
  }

  showPreview() {
    if (!this.data.uuid) {
      return
    }

    const audio = document.createElement('audio')
    audio.title = this.data.title
    this.html.appendChild(audio)

    const {$i18n, $variables} = useNuxtApp()
    const t = $i18n.t

    const useDownload = $variables.value.DOWNLOAD_MEDIA_ENABLED === '1'
    if (useDownload) {
      const size = this.data.size ? prettyBytes(this.data.size) : 0

      const wrapper = document.createElement('div')
      wrapper.classList.add('text-center')
      const link = document.createElement('a')
      link.classList.add('btn', 'btn-link')
      link.href = getApiUrl() + 'audio/' + this.data.uuid
      link.innerHTML = icon(faDownload).html[0] + ' ' + t('models.file.download', {size})

      wrapper.appendChild(link)
      this.html.appendChild(wrapper)
    }

    initAudioPlayer(this.data.uuid, audio)
  }
}
