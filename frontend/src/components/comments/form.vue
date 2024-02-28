<template>
  <b-form @submit.prevent="submit" @keydown="onKeydown">
    <b-form-group>
      <editor-js
        ref="editor"
        v-model="record.content"
        :blocks="editorBlocks"
        :autofocus="autofocus"
        :min-height="50"
        :upload-url="uploadUrl"
      />
    </b-form-group>

    <div class="d-flex justify-content-between align-items-center">
      <b-button v-if="onCancel && (record.id || record.parent_id)" @click="onCancel">
        {{ $t('actions.cancel') }}
      </b-button>
      <div class="timer">{{ editingTime }}</div>
      <b-button variant="primary" type="submit" :disabled="!canSubmit || !canSubmit(record)">
        {{ $t('actions.submit') }}
        <b-spinner v-if="loading" small />
      </b-button>
    </div>
  </b-form>
</template>

<script setup lang="ts">
import {intervalToDuration} from 'date-fns'

const props = defineProps({
  modelValue: {
    type: Object as PropType<VespComment>,
    required: true,
  },
  topic: {
    type: Object,
    required: true,
  },
  autofocus: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  timer: {
    type: Number,
    default: undefined,
  },
})
const emit = defineEmits(['update:modelValue'])
const record = computed({
  get() {
    return props.modelValue
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})

const uploadUrl = getApiUrl() + 'web/topics/' + props.topic.uuid + '/comments/upload'
const editorBlocks = String(useRuntimeConfig().public.EDITOR_COMMENT_BLOCKS || 'image,file').split(',')
const editor = ref()
const editingTime = computed(() => {
  if (!props.timer) {
    return ''
  }
  const duration = intervalToDuration({start: 0, end: props.timer * 1000})
  const zeroPad = (num: number = 0) => String(num).padStart(2, '0')

  return zeroPad(duration.minutes) + ':' + zeroPad(duration.seconds)
})

function submit() {
  if (canSubmit && onSubmit && canSubmit(record.value)) {
    onSubmit(record.value)
  }
}

function reset() {
  if (editor.value) {
    editor.value.reset()
  }
}

const canSubmit = inject<(comment: VespComment) => boolean>('canSubmit')
const onSubmit = inject<(comment: VespComment) => void>('onSubmit')
const onCancel = inject<() => void>('onCancel')

function onKeydown(e: KeyboardEvent) {
  if ((e.metaKey || e.ctrlKey) && e.key === 's') {
    e.preventDefault()
    submit()
  }
}

defineExpose({reset})
</script>
