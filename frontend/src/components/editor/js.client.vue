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
import type {OutputData, LogLevels, I18nDictionary} from '@editorjs/editorjs'
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
import Paragraph from './blocks/paragraph'

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
    type: String,
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
const messages: ComputedRef<I18nDictionary | undefined> = computed(() => {
  return locale.value === 'ru'
    ? {
        blockTunes: {
          delete: {Delete: 'Удалить', 'Click to delete': 'Вы уверены?'},
          moveUp: {'Move up': 'Наверх'},
          moveDown: {'Move down': 'Вниз'},
        },
        toolNames: {
          Text: 'Текст',
          Heading: 'Заголовок',
          List: 'Список',
          File: 'Файл',
          Image: 'Фото',
          Audio: 'Аудио',
          Video: 'Видео',
          Code: 'Код',
        },
        tools: {
          link: {'Add a link': 'Вставьте ссылку'},
          header: {
            'Heading 2': 'Заголовок 2',
            'Heading 3': 'Заголовок 3',
            'Heading 4': 'Заголовок 4',
            'Heading 5': 'Заголовок 5',
          },
          list: {Ordered: 'Нумерованный', Unordered: 'Маркированный'},
          image: {Width: 'Ширина', Height: 'Высота', Crop: 'Обрезка'},
        },
        ui: {
          inlineToolbar: {converter: {'Convert to': 'Конвертировать в'}},
          popover: {
            Filter: 'Поиск',
            'Nothing found': 'Не найдено',
            'Convert to': 'Конвертировать',
          },
        },
      }
    : undefined
})
const allBlocks = [
  {type: 'paragraph', class: Paragraph, toolbar: true},
  {
    type: 'header',
    icon: 'heading',
    class: Header,
    toolbar: true,
    config: {
      placeholder: t('actions.editor.header'),
      levels: [2, 3, 4, 5],
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
  if (props.blocks.length) {
    const types = props.blocks.split(',').map((i) => i.trim().toLowerCase())
    types.push('paragraph')
    return allBlocks.filter((i) => types.includes(i.type))
  }
  return allBlocks
})
const tools: ComputedRef<Record<string, any>> = computed(() => {
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
