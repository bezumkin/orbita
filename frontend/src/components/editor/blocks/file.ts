import type {API, BlockAPI, BlockTool, BlockToolConstructorOptions, BlockToolData} from '@editorjs/editorjs'
import type {HttpRequest, UploadOptions} from 'tus-js-client'
import {DetailedError, Upload} from 'tus-js-client'
import {icon} from '@fortawesome/fontawesome-svg-core'
import {faTimes, faFile} from '@fortawesome/free-solid-svg-icons'
import prettyBytes from 'pretty-bytes'

export default class implements BlockTool {
  api: API
  data: BlockToolData
  html: HTMLElement
  block?: BlockAPI
  upload?: Upload

  constructor({api, data, block}: BlockToolConstructorOptions) {
    this.api = api
    this.data = data
    this.block = block
    this.html = document.createElement('div')
  }

  render() {
    if (this.data) {
      if (this.data.file && this.data.file instanceof File) {
        this.startUpload(this.data.file)
      } else {
        this.showPreview()
      }
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
      endpoint: getApiUrl() + 'admin/topics/upload',
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
        console.error(e)
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
      title.classList.add('fw-medium')
      title.style.textOverflow = 'ellipsis'
      title.style.overflow = 'hidden'
      title.textContent = this.data.title

      fileData.appendChild(title)
      fileData.style.width = '85%'
    }
    if (this.data.size) {
      const size = document.createElement('div')
      size.classList.add('small', 'text-muted')
      size.textContent = prettyBytes(this.data.size)
      if (this.data.width && this.data.height) {
        size.textContent += `, ${this.data.width} x ${this.data.height}`
      }
      fileData.appendChild(size)
    }

    const icon = this.getIcon()
    if (icon) {
      icon.classList.add('fa-fw', 'fa-2x', 'me-3')
      wrapper.appendChild(icon)
    }
    wrapper.appendChild(fileData)

    this.html.appendChild(wrapper)
  }

  getIcon() {
    const span = document.createElement('span')
    span.innerHTML = icon(faFile).html[0]

    return span.firstElementChild
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
