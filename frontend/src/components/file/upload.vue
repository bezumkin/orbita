<template>
  <section>
    <div
      :class="classes"
      :style="styles"
      @drop.prevent="onAddFile"
      @dragenter.prevent="onDragEnter"
      @dragleave.prevent="onDragLeave"
      @dragover.prevent
      @click="onSelect"
    >
      <img v-if="placeholder && record !== false" :src="placeholderUrl" alt="" />
      <img v-if="record && record.file" :src="record.file" alt="" />
    </div>
    <slot name="actions" v-bind="{select: onSelect, remove: onRemove, cancel: onCancel, value: record, placeholder}">
      <div class="text-center">
        <BButton v-if="record && record.file" variant="link" class="text-danger" @click="onCancel">
          {{ titleCancel }}
        </BButton>
        <BButton v-else-if="canRemove" variant="link" class="text-danger" @click="onRemove">
          {{ titleRemove }}
        </BButton>
      </div>
    </slot>
  </section>
</template>

<script setup lang="ts">
type RecordFile = {
  file?: string
  metadata?: {[key: string]: string | number}
}

const props = defineProps({
  modelValue: {
    type: [Boolean, Object],
    default() {
      return {file: undefined, metadata: undefined}
    },
  },
  height: {
    type: Number,
    default: 200,
  },
  width: {
    type: Number,
    default: null,
  },
  placeholder: {
    type: Object as PropType<VespFile>,
    default() {
      return null
    },
  },
  placeholderParams: {
    type: Object as PropType<Record<string, string>>,
    default() {
      return null
    },
  },
  allowRemoving: {
    type: Boolean,
    default: true,
  },
  accept: {
    type: String,
    default: 'image/jpeg, image/png, image/webp, image/heic',
  },
  wrapperClass: {
    type: [String, Array],
    default: '',
  },
  titleCancel: {
    type: String,
    default() {
      return useI18n().t('actions.cancel')
    },
  },
  titleRemove: {
    type: String,
    default() {
      return useI18n().t('actions.delete')
    },
  },
})
const emit = defineEmits(['update:modelValue'])
const record = computed<RecordFile | boolean>({
  get() {
    return props.modelValue
  },
  set(newValue) {
    emit('update:modelValue', newValue)
  },
})

const dragCount = ref(0)
const classes = computed(() => {
  const cls = ['upload-box']
  if (dragCount.value > 0) {
    cls.push('drag-over')
  }
  if (props.wrapperClass) {
    if (typeof props.wrapperClass === 'string') {
      cls.push(props.wrapperClass)
    } else {
      // @ts-ignore
      cls.concat(props.wrapperClass)
    }
  }
  return cls
})

const styles = computed(() => {
  return {
    height: props.height + 'px',
    width: props.width + 'px',
  }
})

const placeholderUrl = computed(() => {
  if (!props.placeholder || !props.placeholder.uuid) {
    return undefined
  }
  const params: {[key: string]: string | number} = {fit: 'crop'}
  if (props.width) {
    params.w = props.width * 2
  }
  if (props.height) {
    params.h = props.height * 2
  }
  if (props.placeholderParams) {
    Object.keys(props.placeholderParams).forEach((key: string) => {
      params[key] = props.placeholderParams[key]
    })
  }
  return getImageLink(props.placeholder, params)
})

const canRemove = computed(() => {
  return props.placeholder && props.allowRemoving && record.value !== false
})

function onSelect() {
  const input = document.createElement('input')
  input.type = 'file'
  input.multiple = false
  input.accept = props.accept
  input.onchange = (e: Event) => {
    onAddFile((e.target as HTMLInputElement).files as FileList)
  }
  input.click()
}

function onRemove() {
  record.value = false
}
function onCancel() {
  record.value = {file: undefined, metadata: undefined}
}
function onAddFile(files: DragEvent | FileList) {
  if (files instanceof DragEvent) {
    if (!files.dataTransfer) {
      return
    }
    files = files.dataTransfer.files as FileList
  }
  const file = Array.from(files).shift() as File
  dragCount.value = 0
  if (verifyAccept(file.type)) {
    const reader = new FileReader()
    reader.onload = () => {
      record.value = {
        file: reader.result as string,
        metadata: {name: file.name, size: file.size, type: file.type},
      }
    }
    reader.readAsDataURL(file)
  }
}
function onDragEnter() {
  dragCount.value++
}
function onDragLeave() {
  dragCount.value--
}
function verifyAccept(type: string) {
  const allowed = props.accept.split(',').map((i: string) => i.trim())
  return allowed.includes(type) || allowed.includes(type.split('/')[0] + '/*')
}
</script>
