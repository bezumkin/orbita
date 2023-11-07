import Parent from './file'

export default class extends Parent {
  static get pasteConfig() {
    return {
      files: {
        mimeTypes: ['image/*'],
      },
    }
  }

  showPreview() {
    if (!this.data.uuid) {
      return
    }

    const image = document.createElement('img')
    image.classList.add('image', 'img-fluid', 'rounded')
    image.src = getImageLink(this.data as VespFile)
    this.html.appendChild(image)
  }
}
