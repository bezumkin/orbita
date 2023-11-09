<template>
  <div>
    <b-button-group v-if="!readOnly" class="w-100 mb-1">
      <template v-for="action in enabledActions" :key="action.type">
        <b-button v-if="action.click" :variant="btnVariant" :size="btnSize" @click="() => action.click()">
          <fa :icon="action.icon" class="fa-fw" />
          {{ $t('actions.editor.' + action.type) }}
        </b-button>
      </template>
    </b-button-group>
    <div ref="holder" :class="{editorjs: true, 'form-control': !readOnly}"></div>
    <!--<pre>{{ record.blocks }}</pre>-->

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
import RemoteVideoBlock from './blocks/remote-video'
import CodeBlock from './blocks/code'

const props = defineProps({
  modelValue: {
    type: Object as PropType<Record<string, any>>,
    required: true,
  },
  readOnly: {
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
  actions: {
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
const allActions = [
  {type: 'audio', icon: 'music', block: AudioBlock, click: insertAudio},
  {type: 'file', icon: 'file', block: FileBlock, click: insertFile},
  {type: 'image', icon: 'image', block: ImageBlock, click: insertImage},
  {type: 'video', icon: 'video', block: VideoBlock, click: insertVideo},
  {type: 'code', icon: 'code', block: CodeBlock, click: insertCode},
  {type: 'remote-video', icon: undefined, block: RemoteVideoBlock, click: undefined},
]
const enabledActions = computed(() => {
  if (props.actions.length) {
    const types = props.actions.map((i: string) => i.toLowerCase())
    return allActions.filter((i) => types.includes(i.type))
  }
  return allActions
})
const tools = computed(() => {
  const tools: Record<string, any> = {}
  enabledActions.value.forEach((i) => {
    tools[i.type] = i.block
  })
  return tools
})
const showVideos = ref(false)

async function onChange() {
  try {
    record.value = await editor.value.save()
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
onMounted(() => {
  nextTick(() => {
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
  })
})

onUnmounted(() => {
  if (editor.value.destroy) {
    editor.value.destroy()
  }
})
</script>

<style scoped lang="scss">
// stylelint-disable
:deep(.editorjs) {
  .codex-editor__redactor {
    display: flex;
    flex-flow: column nowrap;
    gap: 0.5rem;
  }

  .ce-toolbar__plus,
  .cdx-search-field {
    display: none;
  }

  @media (width < 651px) {
    .ce-toolbar__actions {
      right: unset;
      left: -0.25rem;
    }
  }

  @media (width >= 651px) {
    .ce-block__content,
    .ce-toolbar__content {
      max-width: unset;
    }

    .ce-block__content {
      margin-left: 1rem;
    }

    .ce-toolbar__content {
      margin-left: 1.25rem;
    }
  }
}
</style>
