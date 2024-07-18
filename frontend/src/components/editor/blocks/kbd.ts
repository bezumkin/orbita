// @ts-ignore
import {IconInlineCode} from '@codexteam/icons'
import type {API, InlineTool, BlockToolConstructorOptions} from '@editorjs/editorjs'
import type {MenuConfig} from '@editorjs/editorjs/types/tools'

export default class implements InlineTool {
  private api: API
  private tag = 'KBD'
  public static isInline = true

  constructor({api}: BlockToolConstructorOptions) {
    this.api = api
  }

  public render(): MenuConfig {
    return {
      icon: IconInlineCode,
      name: 'kbd',
      onActivate: () => {
        const termWrapper = this.api.selection.findParentTag(this.tag)
        if (termWrapper) {
          this.unwrap(termWrapper)
        } else {
          const selection = document.getSelection()
          if (selection) {
            this.wrap(selection.getRangeAt(0))
          }
        }
      },
      isActive: () => {
        return !!this.api.selection.findParentTag(this.tag)
      },
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

    const selection = window.getSelection()
    if (selection) {
      const range = selection.getRangeAt(0)
      const unwrappedContent = range.extractContents()
      termWrapper.parentNode?.removeChild(termWrapper)
      range.insertNode(unwrappedContent)
      selection.removeAllRanges()
      selection.addRange(range)
    }
  }

  static get sanitize() {
    return {
      kbd: {},
    }
  }
}
