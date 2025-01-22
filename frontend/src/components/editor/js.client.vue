<template>
  <div class="content-editor">
    <div v-if="!readOnly" class="actions">
      <template v-for="action in enabledBlocks" :key="action.type">
        <BButton v-if="action.config?.click" :variant="btnVariant" :size="btnSize" @click="action.config.click">
          <VespFa :icon="action.icon || action.type" class="fa-fw" />
          {{ $t('actions.editor.' + action.type) }}
        </BButton>
      </template>
    </div>
    <div ref="holder" :class="{editorjs: true, 'form-control': !readOnly}"></div>

    <EditorPickVideo v-if="showVideos" @hidden="showVideos = false" />
  </div>
</template>

<script setup lang="ts">
import type {LogLevels, OutputData} from '@editorjs/editorjs'
import type {BaseButtonVariant, BaseSize} from 'bootstrap-vue-next'
import EditorJS from '@editorjs/editorjs'
// @ts-ignore
import Header from '@editorjs/header'
// @ts-ignore
import List from '@editorjs/list'
import AudioBlock from './blocks/audio'
import FileBlock from './blocks/file'
import ImageBlock from './blocks/image'
import VideoBlock from './blocks/video'
import EmbedBlock from './blocks/embed'
import CodeBlock from './blocks/code'
import Kbd from './blocks/kbd'
import ruLexicon from './lexicons/ru'

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
    default: 'light',
  },
  btnSize: {
    type: String as PropType<keyof BaseSize>,
    default: 'sm',
  },
  blocks: {
    type: [String, Boolean],
    default: '',
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
const {locale, t} = useI18n()
const currentBlockIdx = ref(0)
const messages = computed(() => (locale.value === 'ru' ? ruLexicon : undefined))
const allBlocks = [
  {
    type: 'header',
    icon: 'heading',
    class: Header,
    toolbar: true,
    config: {
      placeholder: t('actions.editor.header'),
      levels: [1, 2, 3, 4, 5, 6],
      defaultLevel: 3,
      click: () => insert('header'),
    },
  },
  {type: 'audio', icon: 'music', class: AudioBlock, config: {uploadUrl: props.uploadUrl, click: () => insert('audio')}},
  {type: 'file', class: FileBlock, config: {uploadUrl: props.uploadUrl, click: () => insert('file')}},
  {type: 'image', class: ImageBlock, config: {uploadUrl: props.uploadUrl, click: () => insert('image')}},
  {type: 'video', class: VideoBlock, config: {click: insertVideo}},
  {type: 'code', class: CodeBlock, config: {click: () => insert('code')}},
  {type: 'list', class: List, toolbar: true, config: {defaultStyle: 'unordered', click: () => insert('list')}},
  {type: 'embed', class: EmbedBlock},
  {type: 'kbd', class: Kbd, shortcut: 'CMD+SHIFT+K'},
]
const enabledBlocks = computed(() => {
  if (typeof props.blocks === 'boolean') {
    return props.blocks ? allBlocks : []
  }
  if (typeof props.blocks === 'string') {
    const blocks: Record<string, any>[] = []
    props.blocks.split(',').forEach((key: string) => {
      const block = allBlocks.find((i) => i.type === key.trim().toLowerCase())
      if (block) {
        blocks.push(block)
      }
    })
    return blocks
  }
  return allBlocks
})
const tools = computed<Record<string, any>>(() => {
  return Object.fromEntries(
    enabledBlocks.value.map((i) => [
      i.type,
      {class: i.class, inlineToolbar: i.toolbar || false, config: i.config || {}, shortcut: i.shortcut},
    ]),
  )
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

function insertVideo() {
  currentBlockIdx.value = editor.value.blocks.getCurrentBlockIndex()
  showVideos.value = true
}

function insert(block: string) {
  editor.value.blocks.insert(block)
}

provide('pickVideo', (video: any) => {
  const data: VespVideo = {
    id: video.file_id,
    uuid: video.id,
    duration: video.duration,
    size: video.file?.size,
    width: video.file?.width,
    height: video.file?.height,
    moved: video.moved,
    updated_at: video.updated_at,
  }
  if (video.audio) {
    data.audio = video.audio.uuid
    data.audio_size = video.audio.size
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
