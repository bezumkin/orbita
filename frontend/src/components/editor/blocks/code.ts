import type {BlockAPI, BlockTool, BlockToolConstructorOptions, BlockToolData} from '@editorjs/editorjs'
import {highlight, languages as PrismLanguages} from 'prismjs'
import 'prismjs/components/prism-bash'
import 'prismjs/components/prism-docker'
import 'prismjs/components/prism-php'
import 'prismjs/components/prism-php-extras'
import 'prismjs/components/prism-ini'
import 'prismjs/components/prism-java'
import 'prismjs/components/prism-json'
import 'prismjs/components/prism-markdown'
import 'prismjs/components/prism-nginx'
import 'prismjs/components/prism-python'
import 'prismjs/components/prism-regex'
import 'prismjs/components/prism-ruby'
import 'prismjs/components/prism-rust'
import 'prismjs/components/prism-sass'
import 'prismjs/components/prism-scss'
import 'prismjs/components/prism-sql'
import 'prismjs/components/prism-plsql'
import 'prismjs/components/prism-typescript'
import 'prismjs/components/prism-yaml'
import 'prismjs/components/prism-markup-templating'
// import '@jongwooo/prism-theme-github/themes/prism-github-default-auto.css'
// @ts-ignore
import CodeFlask from 'codeflask'

export default class implements BlockTool {
  block?: BlockAPI
  data: BlockToolData
  html: HTMLElement
  readOnly: boolean
  wrapper?: HTMLElement
  select?: HTMLSelectElement
  textarea?: HTMLTextAreaElement
  flask?: CodeFlask
  languages: Record<string, string>

  constructor({block, data, readOnly}: BlockToolConstructorOptions) {
    this.block = block
    this.data = data
    this.readOnly = readOnly
    this.html = document.createElement('div')

    this.languages = {
      plain: 'Text',
      bash: 'Bash',
      css: 'CSS',
      docker: 'Docker',
      js: 'Javascript',
      html: 'HTML',
      svg: 'SVG',
      rss: 'RSS',
      ini: 'INI',
      java: 'Java',
      json: 'JSON',
      markdown: 'Markdown',
      nginx: 'Nginx',
      php: 'PHP',
      plsql: 'PostgreSQL',
      python: 'Python',
      regex: 'Regex',
      ruby: 'Ruby',
      rust: 'Rust',
      sass: 'SASS',
      scss: 'SCSS',
      sql: 'SQL',
      typescript: 'TypeScript',
      xml: 'XML',
      yaml: 'Yaml',
    }
  }

  render() {
    if (this.readOnly) {
      this.showPreview()
    } else {
      this.showEditor()
      setTimeout(() => {
        if (!this.data.code) {
          this.textarea?.focus()
        } else {
          this.adjustHeight()
        }
      }, 250)
    }

    return this.html
  }

  save() {
    return {
      language: this.select?.value,
      code: this.textarea?.value,
    }
  }

  static get isReadOnlySupported() {
    return true
  }

  static get enableLineBreaks() {
    return true
  }

  changeLanguage(lang: string, update: boolean = false) {
    if (PrismLanguages[lang]) {
      this.flask?.addLanguage(lang, PrismLanguages[lang])
      this.flask?.updateLanguage(lang)
    }
    if (update) {
      this.block?.dispatchChange()
    }
  }

  adjustHeight() {
    if (this.wrapper && this.textarea) {
      const pre = this.wrapper.getElementsByTagName('pre')
      if (pre.length) {
        this.wrapper.style.height = pre[0].scrollHeight + 2 + 'px'
      }
    }
  }

  showEditor() {
    const select = document.createElement('select')
    select.classList.add('form-select', 'form-select-sm', 'mt-1')
    select.onchange = () => {
      this.changeLanguage(select.value, true)
    }

    Object.keys(this.languages).forEach((value) => {
      const option = document.createElement('option')
      option.value = value
      option.textContent = this.languages[value]
      if (this.data.language && this.data.language === value) {
        option.selected = true
      }
      select.appendChild(option)
    })

    const wrapper = document.createElement('div')
    wrapper.style.minHeight = '100px'
    wrapper.style.position = 'relative'
    wrapper.style.zIndex = '0'

    this.html.appendChild(wrapper)
    this.html.appendChild(select)

    const flask = new CodeFlask(wrapper, {
      language: select.value,
      readonly: this.readOnly,
      defaultTheme: false,
    })
    if (this.data.code) {
      flask.updateCode(this.data.code)
      setTimeout(() => {
        this.changeLanguage(select.value)
      }, 250)
    }
    flask.elWrapper.classList.add('form-control')

    this.flask = flask
    this.select = select
    this.textarea = flask.elTextarea
    this.wrapper = wrapper

    if (this.textarea) {
      this.textarea.onkeydown = () => this.adjustHeight()
      this.textarea.onchange = () => this.adjustHeight()
      this.textarea.oninput = () => this.adjustHeight()
    }
  }

  showPreview() {
    const code = document.createElement('code')

    const tmp = document.createElement('code')
    tmp.innerHTML = highlight(this.data.code, PrismLanguages[this.data.language], this.data.language)
    if (tmp.firstElementChild) {
      tmp.firstElementChild.classList.forEach((i) => {
        code.classList.add(i)
      })
      code.classList.add('d-block', 'p-3')
      code.innerHTML = tmp.firstElementChild.innerHTML
    }

    const pre = document.createElement('pre')
    pre.classList.add('rounded')
    pre.appendChild(code)

    this.html.appendChild(pre)
  }

  static get pasteConfig() {
    return {
      tags: ['pre'],
    }
  }

  onPaste(e: CustomEvent) {
    console.log(e)
    const content = e.detail.data

    this.data = {
      language: 'text',
      code: content.textContent,
    }
  }
}
