import type {BlockAPI, BlockTool, BlockToolConstructorOptions, BlockToolData} from '@editorjs/editorjs'
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
    const {$prismLanguages} = useNuxtApp()
    if ($prismLanguages[lang]) {
      this.flask?.addLanguage(lang, $prismLanguages[lang])
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
    try {
      const {$prism} = useNuxtApp()
      const code = document.createElement('code')
      code.innerHTML = $prism(this.data.code, this.data.language)

      const pre = document.createElement('pre')
      pre.classList.add('rounded')
      pre.appendChild(code)

      this.html.appendChild(pre)
    } catch (e) {
      console.error(e)
    }
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
