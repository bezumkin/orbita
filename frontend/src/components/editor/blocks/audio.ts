import {icon} from '@fortawesome/fontawesome-svg-core'
import {faMusic} from '@fortawesome/free-solid-svg-icons'
import Plyr from 'plyr'
import Parent from './file'

export default class extends Parent {
  static get pasteConfig() {
    return {
      files: {
        mimeTypes: ['audio/*'],
      },
    }
  }

  getIcon() {
    const span = document.createElement('span')
    span.innerHTML = icon(faMusic).html[0]

    return span.firstElementChild
  }

  showPreview() {
    if (!this.data.uuid) {
      return
    }

    const audio = document.createElement('audio')
    audio.src = getApiUrl() + 'audio/' + this.data.uuid

    this.html.classList.add('rounded', 'border', 'p-2')
    this.html.appendChild(audio)
    new Plyr(audio)
  }
}
