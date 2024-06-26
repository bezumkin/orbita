import type {API, BlockAPI, BlockTool, BlockToolConstructorOptions, BlockToolData, ToolConfig} from '@editorjs/editorjs'
import type {HttpRequest, UploadOptions} from 'tus-js-client'
import {DetailedError, Upload} from 'tus-js-client'
import {icon} from '@fortawesome/fontawesome-svg-core'
import {faTimes, faCloudArrowDown} from '@fortawesome/free-solid-svg-icons'
import prettyBytes from 'pretty-bytes'

export function getDescription(file: VespFile) {
  const parts = []
  if (file.size) {
    parts.push(prettyBytes(file.size))
  }
  if (file.type) {
    parts.push(file.type)
  }
  if (file.width && file.height) {
    parts.push(file.width + ' x ' + file.height)
  }
  return parts.join(', ')
}

export default class implements BlockTool {
  api: API
  data: BlockToolData
  html: HTMLElement
  block?: BlockAPI
  upload?: Upload
  config?: ToolConfig
  title?: string
  type: string = 'file'

  constructor({api, data, block, config}: BlockToolConstructorOptions) {
    this.api = api
    this.data = data
    this.block = block
    this.config = config
    this.html = document.createElement('div')
  }

  static get toolbox() {
    return {
      title: 'File',
      icon: '<svg class="svg-inline--fa fa-fw" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M0 64C0 28.7 28.7 0 64 0H224V128c0 17.7 14.3 32 32 32H384V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V64zm384 64H256V0L384 128z"></path></svg>',
    }
  }

  render() {
    if (this.data && Object.keys(this.data).length) {
      if (this.data.file && this.data.file instanceof File) {
        this.startUpload(this.data.file)
      } else {
        this.showPreview()
      }
    } else {
      const input = document.createElement('input')
      input.type = 'file'
      input.multiple = false
      if (this.type === 'image') {
        input.accept = 'image/*'
      } else if (this.type === 'audio') {
        input.accept = 'audio/*'
      }
      input.onchange = ({target}: Event) => {
        const files: FileList | null = (target as HTMLInputElement).files
        if (files) {
          this.api.blocks.insert(this.type, {file: files[0]})
        }
      }
      input.click()
    }

    return this.html
  }

  save() {
    return this.data && this.data.uuid ? this.data : null
  }

  static get isReadOnlySupported() {
    return true
  }

  showProgress() {
    const progressBar = document.createElement('div')
    progressBar.classList.add('progress-bar', 'bg-primary')
    progressBar.style.width = '0%'

    const progress = document.createElement('div')
    progress.classList.add('progress', 'w-100')
    progress.appendChild(progressBar)

    const button = document.createElement('button')
    button.classList.add('btn', 'btn-danger', 'btn-sm', 'px-2', 'py-0', 'ms-1')
    button.innerHTML = icon(faTimes).html[0]
    button.onclick = (e) => {
      e.preventDefault()
      this.abortUpload()
    }

    const wrapper = document.createElement('div')
    wrapper.classList.add('progress-wrapper', 'd-flex', 'align-items-center')
    wrapper.appendChild(progress)
    wrapper.appendChild(button)

    this.html.appendChild(wrapper)
  }

  hideProgress() {
    const node = this.html.querySelector('.progress-wrapper')
    if (node) {
      node.remove()
    }
  }

  onProgress(loaded: number, total: number) {
    const node = this.html.querySelector('.progress-bar')
    if (node instanceof HTMLElement) {
      const done = Math.round((loaded / total) * 100) + '%'
      node.style.width = done
      node.textContent = done
    }
  }

  async onSuccess() {
    if (this.upload && this.upload.url) {
      const file = await useGet(this.upload.url)
      this.data = {
        id: file.id,
        uuid: file.uuid,
        title: file.title,
        size: file.size,
        type: file.type,
        width: file.width,
        height: file.height,
        updated_at: file.updated_at,
      }
      this.hideProgress()
      this.showPreview()
    }
  }

  async startUpload(file: File) {
    this.createUpload(file)
    if (!this.upload) {
      return
    }
    try {
      const previousUploads = await this.upload.findPreviousUploads()
      if (previousUploads.length) {
        this.upload.resumeFromPreviousUpload(previousUploads[0])
      }
      this.showProgress()
      this.upload.start()
    } catch (e) {
      console.error(e)
    }
  }

  abortUpload() {
    if (this.upload) {
      this.upload.abort(true)
    }

    if (this.block) {
      const id = this.block.id
      const idx = this.api.blocks.getBlockIndex(id)
      this.api.blocks.delete(idx)
    }
  }

  createUpload(file: File) {
    const $this = this

    const options: UploadOptions = {
      retryDelays: [],
      endpoint: this.config.uploadUrl,
      metadata: {filename: file.name, filetype: file.type},
      chunkSize: 104857600,
      removeFingerprintOnSuccess: true,
      onBeforeRequest(req: HttpRequest) {
        const {token} = useAuth()
        if (token.value) {
          req.setHeader('Authorization', token.value)
        }
      },
      onProgress(loaded, total) {
        $this.onProgress(loaded, total)
      },
      onChunkComplete(_, loaded, total) {
        $this.onProgress(loaded, total)
      },
      onSuccess() {
        $this.onSuccess()
      },
      onError(e: Error | DetailedError) {
        if ('originalResponse' in e) {
          useToastError(JSON.parse(e.originalResponse?.getBody() as string))
          const status = e.originalResponse?.getStatus() as number
          if (status >= 400 && status < 500) {
            $this.abortUpload()
          }
        } else {
          console.error(e)
        }
      },
    }

    this.upload = new Upload(file, options)
  }

  showPreview() {
    if (!this.data.uuid) {
      return
    }
    const wrapper = document.createElement('div')
    wrapper.classList.add('d-flex', 'align-items-center', 'rounded', 'border', 'p-3')

    const fileData = document.createElement('div')
    fileData.classList.add('d-flex', 'flex-column')
    if (this.data.title) {
      const title = document.createElement('div')
      title.classList.add('fw-medium', 'text-break')
      title.textContent = this.data.title
      fileData.appendChild(title)
    }
    const description = document.createElement('div')
    description.classList.add('small', 'text-muted')
    description.textContent = getDescription(this.data)
    fileData.appendChild(description)

    const button = this.getButton()
    if (button) {
      wrapper.appendChild(button)
    }
    wrapper.appendChild(fileData)

    this.html.appendChild(wrapper)
  }

  getButton() {
    const span = document.createElement('span')
    span.innerHTML = icon(faCloudArrowDown).html[0]
    span.firstElementChild?.classList.add('fa-fw')

    const btn = document.createElement('button')
    btn.classList.add('btn', 'btn-light', 'btn-lg', 'me-3')
    if (span.firstElementChild) {
      btn.appendChild(span.firstElementChild)
    }

    btn.onclick = (e) => {
      e.preventDefault()
      e.stopPropagation()
      window.location.href = getFileLink(this.data)
    }

    return btn
  }

  static get pasteConfig() {
    return {
      files: {
        mimeTypes: ['application/*', 'video/*', 'text/*', 'font/*'],
      },
    }
  }

  onPaste(event: CustomEvent) {
    if (event.detail.file && event.detail.file instanceof File) {
      this.startUpload(event.detail.file)
    }
  }
}
