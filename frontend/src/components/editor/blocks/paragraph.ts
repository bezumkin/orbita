// @ts-ignore
import Paragraph, {type ParagraphData, ParagraphParams} from '@editorjs/paragraph'

export default class extends Paragraph {
  private _data: ParagraphData
  private _element: HTMLDivElement | null = null

  constructor(params: ParagraphParams) {
    super(params)
    this._data = params.data ?? {}
    this._element = null
  }

  static get pasteConfig() {
    return {
      ...super.pasteConfig,
      patterns: {
        link: /https?:\/\/\S+/i,
      },
    }
  }

  onPaste(event: CustomEvent) {
    if (event.type === 'pattern' && event.detail.key === 'link') {
      const text = `<a href="${event.detail.data}">${event.detail.data}</a>`
      this._data = {text}
      window.requestAnimationFrame(() => {
        if (this._element) {
          this._element.innerHTML = text
        }
      })
    } else {
      super.onPaste(event)
    }
  }
}
