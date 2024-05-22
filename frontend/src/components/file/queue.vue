<template>
  <div
    :class="classes"
    @drop.prevent="onDropFiles"
    @dragenter.prevent="onDragEnter"
    @dragleave.prevent="onDragLeave"
    @dragover.prevent
    @click="onUpload"
  >
    <div class="upload-items">
      <transition-group name="fade">
        <div v-for="item in uploading" :key="item.id" class="p-3 bg-white border rounded">
          <div class="fw-bold">{{ item.filename }}</div>
          <div class="d-flex align-items-center gap-2">
            <b-progress class="w-100">
              <b-progress-bar :value="item.progress" v-bind="getProgressParams(item)">
                {{ item.progress }}%
              </b-progress-bar>
            </b-progress>
            <div class="d-flex gap-2">
              <template v-if="!item.finished && !item.error">
                <b-button v-if="!item.paused" @click.stop="pauseUpload(item)">
                  <vesp-fa icon="pause" />
                </b-button>
                <template v-else>
                  <b-button variant="success" @click.stop="continueUpload(item)">
                    <vesp-fa icon="play" />
                  </b-button>
                  <b-button variant="danger" @click.stop="cancelUpload(item)">
                    <vesp-fa icon="times" />
                  </b-button>
                </template>
              </template>
              <b-button v-else-if="item.finished" variant="outline-secondary" @click.stop="removeUpload(item)">
                <vesp-fa icon="check" />
              </b-button>
              <b-button v-else-if="item.error" variant="danger" @click.stop="cancelUpload(item)">
                <vesp-fa icon="times" />
              </b-button>
            </div>
          </div>
          <div class="small">
            {{ getStatus(item) }}
          </div>
        </div>
      </transition-group>
    </div>
  </div>
</template>

<script setup lang="ts">
import prettyBytes from 'pretty-bytes'
import {formatDistanceToNow, formatDuration, fromUnixTime, intervalToDuration} from 'date-fns'
import ru from 'date-fns/locale/ru/index.js'
import de from 'date-fns/locale/de/index.js'
import {DetailedError, Upload} from 'tus-js-client'
import type {HttpRequest, UploadOptions} from 'tus-js-client'
import type {BaseColorVariant} from 'bootstrap-vue-next'

const props = defineProps({
  accept: {
    type: String,
    default: '',
  },
  endpoint: {
    type: String,
    default: 'admin/videos/upload',
  },
})
const emit = defineEmits(['success', 'error'])

type UploadItem = {
  id: string
  filename: string
  filetype: string
  paused: boolean
  finished: boolean
  start: number
  end?: number
  progress: string
  size: number
  uploaded: number
  speed?: number
  remaining?: Date
  duration?: Duration
  error?: Error
}

const {t} = useI18n()
const {token} = useAuth()
const locale = computed(() => {
  const code = useI18n().locale.value
  if (code === 'ru') {
    return ru
  }
  if (code === 'de') {
    return de
  }
  return undefined
})

const uploading: Ref<UploadItem[]> = ref([])
const uploads: Record<string, Upload> = {}

const dragCount = ref(0)
const classes = computed(() => {
  const cls = ['upload-box', 'p-2']
  if (dragCount.value > 0) {
    cls.push('drag-over')
  }
  if (uploading.value.length) {
    cls.push('uploading')
  }
  return cls
})

function formatSize(size: number, speed?: boolean): string {
  return prettyBytes(size) + (speed ? '/s' : '')
}

function onUpload() {
  const input = document.createElement('input')
  input.type = 'file'
  input.multiple = true
  input.accept = props.accept
  input.onchange = (e: Event) => {
    const target = e.target as HTMLInputElement
    if (target.files) {
      ;[...target.files].forEach((item) => {
        createUpload(item)
      })
    }
  }
  input.click()
}

function createUpload(file: File) {
  if (!verifyAccept(file.type)) {
    return
  }
  const id = Math.random().toString(16).slice(2)
  const item: UploadItem = {
    id,
    paused: false,
    finished: false,
    start: Number(new Date()),
    progress: '0.00',
    uploaded: 0,
    filename: file.name,
    filetype: file.type,
    size: file.size,
  }

  const options: UploadOptions = {
    retryDelays: [],
    endpoint: getApiUrl() + props.endpoint,
    metadata: {filename: file.name, filetype: file.type},
    chunkSize: 104857600,
    removeFingerprintOnSuccess: true,
    onBeforeRequest(req: HttpRequest) {
      if (token.value) {
        req.setHeader('Authorization', token.value)
      }
    },
    onProgress(bytesUploaded, bytesTotal) {
      const item = uploading.value.find((i) => i.id === id)
      if (item) {
        onProgress(item, bytesUploaded, bytesTotal)
      }
    },
    onChunkComplete(_, bytesUploaded, bytesTotal) {
      const item = uploading.value.find((i) => i.id === id)
      if (item) {
        onProgress(item, bytesUploaded, bytesTotal)
      }
    },
    onError(e: Error | DetailedError) {
      const item = uploading.value.find((i) => i.id === id)
      if (item) {
        item.error = e
        emit('error', item)
      }
      // useToastError(e.message)
    },
    onSuccess() {
      const item = uploading.value.find((i) => i.id === id)
      if (item) {
        item.end = Number(new Date())
        item.duration = intervalToDuration({start: item.start, end: item.end})
        item.paused = false
        item.finished = true
        emit('success', item)
      }
    },
  }

  uploads[item.id] = new Upload(file, options)
  uploading.value.push(item)
  startUpload(item)
}

async function startUpload(item: UploadItem) {
  const upload = uploads[item.id]
  try {
    const previousUploads = await upload.findPreviousUploads()
    if (previousUploads.length) {
      upload.resumeFromPreviousUpload(previousUploads[0])
    }
    upload.start()
    item.start = Number(new Date())
  } catch (e) {
    console.error(e)
  }
}

async function pauseUpload(item: UploadItem) {
  const upload = uploads[item.id]
  await upload.abort()
  item.paused = true
}

function continueUpload(item: UploadItem) {
  const upload = uploads[item.id]
  upload.start()
  item.paused = false
  item.start = Number(new Date())
}

async function cancelUpload(item: UploadItem) {
  removeUpload(item)
  try {
    const upload = uploads[item.id]
    await upload.abort(true)
  } catch (e) {}
}

function removeUpload(item: UploadItem) {
  delete uploads[item.id]
  uploading.value = uploading.value.filter((i) => i.id !== item.id)
}

function onProgress(item: UploadItem, bytesUploaded: number, bytesTotal: number) {
  const now = Number(new Date())
  item.progress = ((bytesUploaded / bytesTotal) * 100).toFixed(2)
  item.speed = bytesUploaded / ((now - item.start) / 1000)
  item.uploaded = bytesUploaded
  const timeRemaining = (bytesTotal - bytesUploaded) / item.speed
  if (timeRemaining !== Infinity) {
    item.remaining = fromUnixTime(now / 1000 + timeRemaining)
  }
}

function getStatus(item: UploadItem) {
  if (item.error) {
    if (item.error instanceof DetailedError) {
      const status = item.error.originalResponse?.getStatus() || 500
      const message = JSON.parse(item.error.originalResponse?.getBody() as string)
      const translate = translateServerMessage(message)

      return message !== translate
        ? translate
        : t('components.upload.status_error', {
            error: message ? t(message) : 'HTTP status ' + status,
          })
    }
    return String(item.error)
  } else if (item.finished) {
    if (item.duration && item.speed) {
      return t('components.upload.status_finished', {
        size: formatSize(item.size),
        duration: formatDuration(item.duration, {locale: locale.value}),
        speed: formatSize(item.speed, true),
      })
    }
  } else if (item.paused) {
    return t('components.upload.status_paused', {uploaded: formatSize(item.uploaded), size: formatSize(item.size)})
  } else if (item.remaining && item.speed) {
    return t('components.upload.status_loading', {
      uploaded: formatSize(item.uploaded),
      size: formatSize(item.size),
      remaining: formatDistanceToNow(item.remaining, {includeSeconds: true, locale: locale.value}),
      speed: formatSize(item.speed, true),
    })
  }
  return ''
}

function getProgressParams(item: UploadItem): {variant: keyof BaseColorVariant; striped: boolean; animated: boolean} {
  if (item.error || item.finished) {
    return {variant: item.error ? 'danger' : 'success', striped: false, animated: false}
  }

  return {variant: 'primary', striped: true, animated: !item.paused}
}

function onDropFiles({dataTransfer}: DragEvent) {
  dragCount.value = 0
  if (!dataTransfer) {
    return
  }
  const files = dataTransfer.files as FileList
  ;[...files].forEach((file) => {
    createUpload(file)
  })
}

function onDragEnter() {
  dragCount.value++
}

function onDragLeave() {
  dragCount.value--
}

function verifyAccept(type: string) {
  if (!props.accept) {
    return true
  }
  const allowed = props.accept.split(',').map((i: string) => i.trim())
  return allowed.includes(type) || allowed.includes(type.split('/')[0] + '/*')
}
</script>
