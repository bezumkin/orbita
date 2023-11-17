<template>
  <div>
    <b-button-group v-if="!readOnly" class="w-100 mb-1">
      <template v-for="action in enabledBlocks" :key="action.type">
        <b-button v-if="action.click" :variant="btnVariant" :size="btnSize" @click="() => action.click()">
          <fa :icon="action.icon" class="fa-fw" />
          {{ $t('actions.editor.' + action.type) }}
        </b-button>
      </template>
    </b-button-group>
    <div ref="holder" :class="{editorjs: true, 'form-control': !readOnly}"></div>

    <editor-pick-video v-if="showVideos" @hidden="showVideos = false" />
  </div>
</template>

<script setup lang="ts">
import type {OutputData, LogLevels, I18nDictionary} from '@editorjs/editorjs'
import type {BaseButtonVariant, BaseSize} from 'bootstrap-vue-next/src/types'
import EditorJS from '@editorjs/editorjs'
import AudioBlock from './blocks/audio'
import FileBlock from './blocks/file'
import ImageBlock from './blocks/image'
import VideoBlock from './blocks/video'
import EmbedBlock from './blocks/embed'
import CodeBlock from './blocks/code'

const props = defineProps({
  modelValue: {
    type: Object as PropType<Record<string, any>>,
    required: true,
  },
  uploadUrl: {
    type: String,
    default() {
      return getApiUrl() + 'admin/topics/upload'
    },
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  autofocus: {
    type: Boolean,
    default: false,
  },
  minHeight: {
    type: Number,
    default: 100,
  },
  btnVariant: {
    type: String as PropType<keyof BaseButtonVariant>,
    default: 'outline-secondary',
  },
  btnSize: {
    type: String as PropType<keyof BaseSize>,
    default: 'sm',
  },
  blocks: {
    type: Array as PropType<string[]>,
    default() {
      return []
    },
  },
})
const emit = defineEmits(['update:modelValue'])
const record = computed({
  get() {
    return props.modelValue || {blocks: []}
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})

const holder = ref()
const editor = ref()
const {locale} = useI18n()
const currentBlockIdx = ref(0)
const messages: ComputedRef<I18nDictionary | undefined> = computed(() => {
  return locale.value === 'ru'
    ? {
        blockTunes: {
          delete: {Delete: 'Удалить', 'Click to delete': 'Вы уверены?'},
          moveUp: {'Move up': 'Наверх'},
          moveDown: {'Move down': 'Вниз'},
        },
        toolNames: {},
        tools: {link: {'Add a link': 'Вставьте ссылку'}},
        ui: {},
      }
    : undefined
})
const allBlocks = [
  {type: 'audio', icon: 'music', class: AudioBlock, click: insertAudio, config: {uploadUrl: props.uploadUrl}},
  {type: 'file', icon: 'file', class: FileBlock, click: insertFile, config: {uploadUrl: props.uploadUrl}},
  {type: 'image', icon: 'image', class: ImageBlock, click: insertImage, config: {uploadUrl: props.uploadUrl}},
  {type: 'video', icon: 'video', class: VideoBlock, click: insertVideo, config: {uploadUrl: props.uploadUrl}},
  {type: 'code', icon: 'code', class: CodeBlock, click: insertCode},
  {type: 'embed', icon: undefined, class: EmbedBlock, click: undefined},
]
const enabledBlocks = computed(() => {
  if (props.blocks.length) {
    const types = props.blocks.map((i: string) => i.toLowerCase())
    return allBlocks.filter((i) => types.includes(i.type))
  }
  return allBlocks
})
const tools: ComputedRef<Record<string, any>> = computed(() => {
  return Object.fromEntries(enabledBlocks.value.map((i) => [i.type, {class: i.class, config: i.config || {}}]))
})
const showVideos = ref(false)

async function onChange() {
  try {
    if (editor.value && typeof editor.value.save === 'function') {
      record.value = await editor.value.save()
    }
  } catch (e) {
    console.error(e)
  }
}

function insertFile(type: string = 'file') {
  const input = document.createElement('input')
  input.type = 'file'
  input.multiple = false
  if (type === 'image') {
    input.accept = 'image/*'
  } else if (type === 'audio') {
    input.accept = 'audio/*'
  }
  input.onchange = ({target}: Event) => {
    const files: FileList | null = (target as HTMLInputElement).files
    if (files) {
      editor.value.blocks.insert(type, {file: files[0]})
    }
  }
  input.click()
}

function insertAudio() {
  insertFile('audio')
}

function insertImage() {
  insertFile('image')
}

function insertVideo() {
  currentBlockIdx.value = editor.value.blocks.getCurrentBlockIndex()
  showVideos.value = true
}

function insertCode() {
  editor.value.blocks.insert('code')
}

provide('pickVideo', (video: any) => {
  const data = {
    id: video.file_id,
    uuid: video.id,
    updated_at: video.updated_at,
  }
  editor.value.focus()
  editor.value.blocks.insert('video', data, {}, currentBlockIdx.value + 1)
})

function initEditor() {
  try {
    editor.value = new EditorJS({
      holder: holder.value,
      data: record.value as OutputData,
      minHeight: props.minHeight,
      hideToolbar: true,
      logLevel: 'ERROR' as LogLevels,
      readOnly: props.readOnly,
      onChange,
      i18n: {messages: messages.value},
      tools: tools.value,
    })
    if (props.autofocus) {
      setTimeout(() => {
        if (editor.value && editor.value.caret) {
          editor.value.caret.focus(true)
        }
      }, 100)
    }
  } catch (e) {}
}

function resetEditor() {
  if (editor.value) {
    if (editor.value.destroy) {
      editor.value.destroy()
    }
  }
  record.value = {data: []}
  nextTick(initEditor)
}

onMounted(() => {
  nextTick(initEditor)
})

onUnmounted(() => {
  if (editor.value.destroy) {
    editor.value.destroy()
  }
})
defineExpose({reset: resetEditor})
</script>
