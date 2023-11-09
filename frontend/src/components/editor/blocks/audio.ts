import Parent from './file'

export default class extends Parent {
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

    const title = document.createElement('div')
    title.classList.add('px-4', 'fw-medium', 'text-break')
    title.textContent = this.data.title

    const audioWrapper = document.createElement('div')
    audioWrapper.classList.add('px-2')

    const audio = document.createElement('audio')
    audio.src = getApiUrl() + 'audio/' + this.data.uuid
    audioWrapper.appendChild(audio)

    this.html.classList.add('py-3', 'border', 'rounded')
    this.html.appendChild(title)
    this.html.appendChild(audioWrapper)
    useNuxtApp().$plyr(audio)
  }
}
