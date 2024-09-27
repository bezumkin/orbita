<template>
  <div>
    <div class="ratio ratio-1x1">
      <div
        :class="{'avatar-crop-wrapper': true, active: dragCount > 0}"
        @click="onClick"
        @drop.prevent="onAddFile"
        @dragenter.prevent="onDragEnter"
        @dragleave.prevent="onDragLeave"
        @dragover.prevent
      >
        <img ref="image" :src="record.file" alt="" class="d-block img-fluid" />
      </div>
    </div>
    <slot name="actions" v-bind="{select: onSelect}" />
  </div>
</template>

<script setup lang="ts">
import Cropper from 'cropperjs'

const props = defineProps({
  modelValue: {
    type: Object as PropType<VespUser>,
    required: true,
  },
  required: {
    type: Boolean,
    default: false,
  },
  size: {
    type: [Number, String],
    default: 200,
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

const image = ref()
const cropper = ref()
const dragCount = ref(0)

function initCropper() {
  cropper.value = new Cropper(image.value, {
    aspectRatio: 1,
    viewMode: 1,
    autoCrop: true,
    data: record.value.metadata.crop,
    movable: false,
    rotatable: false,
    scalable: false,
    zoomable: false,
    crop: () => {
      const value = record.value
      value.metadata.crop = cropper.value.getData(true)
      record.value = value
    },
  })
}

function destroyCropper() {
  if (cropper.value && 'destroy' in cropper.value) {
    cropper.value.destroy()
  }
  cropper.value = undefined
}

function replaceFile(file, crop: Record<string, number> | undefined) {
  if (!cropper.value) {
    initCropper()
  }
  cropper.value.replace(file)
  if (crop) {
    setTimeout(() => {
      cropper.value.setData(crop)
    }, 100)
  }
}

function onSelect() {
  const input = document.createElement('input')
  input.type = 'file'
  input.multiple = false
  input.accept = 'image/*'
  input.onchange = (e) => {
    onAddFile({dataTransfer: {files: e.target.files}})
  }
  input.click()
}

function onClick() {
  if (!record.value.file) {
    onSelect()
  }
}

function onAddFile({dataTransfer}) {
  dragCount.value = 0
  const file = Array.from(dataTransfer.files).shift()
  if (file.type.includes('image/')) {
    const reader = new FileReader()
    reader.onload = () => {
      record.value.file = reader.result
      record.value.metadata = {name: file.name, size: file.size, type: file.type, crop: null}
      replaceFile(reader.result)
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

watch(
  () => props.modelValue,
  (newValue) => {
    if (!newValue.file) {
      destroyCropper()
    } else {
      replaceFile(newValue.file, newValue.metadata?.crop)
    }
  },
)

onMounted(() => {
  if (record.value.file) {
    initCropper()
  }
})

onUnmounted(destroyCropper)
</script>
