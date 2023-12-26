// @ts-ignore
import {IconInlineCode} from '@codexteam/icons'
import type {API, InlineTool, BlockToolConstructorOptions} from '@editorjs/editorjs'

export default class implements InlineTool {
  api: API
  button?: HTMLButtonElement
  tag: string
  iconClasses: Record<string, any>

  constructor({api}: BlockToolConstructorOptions) {
    this.api = api
    this.iconClasses = {
      base: this.api.styles.inlineToolButton,
      active: this.api.styles.inlineToolButtonActive,
    }
    this.tag = 'KBD'
  }

  static get isInline() {
    return true
  }

  render() {
    this.button = document.createElement('button')
    this.button.type = 'button'
    this.button.classList.add(this.iconClasses.base)
    this.button.innerHTML = this.toolboxIcon

    return this.button
  }

  surround(range?: Range) {
    if (!range) {
      return
    }

    const termWrapper = this.api.selection.findParentTag(this.tag)

    if (termWrapper) {
      this.unwrap(termWrapper)
    } else {
      this.wrap(range)
    }
  }

  wrap(range: Range) {
    const tag = document.createElement(this.tag)
    tag.appendChild(range.extractContents())
    range.insertNode(tag)

    this.api.selection.expandToTag(tag)
  }

  unwrap(termWrapper: HTMLElement) {
    this.api.selection.expandToTag(termWrapper)

    const sel = window.getSelection()
    if (sel) {
      const range = sel.getRangeAt(0)
      const unwrappedContent = range.extractContents()
      termWrapper.parentNode?.removeChild(termWrapper)
      range.insertNode(unwrappedContent)
      sel.removeAllRanges()
      sel.addRange(range)
    }
  }

  checkState() {
    const parentTag = this.api.selection.findParentTag(this.tag)
    const active = parentTag?.tagName === this.tag
    this.button?.classList.toggle(this.iconClasses.active, active)
    return active
  }

  get toolboxIcon() {
    return IconInlineCode
  }

  static get sanitize() {
    return {
      kbd: {},
    }
  }
}
