<template>
  <div>
    <video v-if="isVideo" ref="video" v-bind="videoProps">
      <source :src="$file(block.data)" :type="block.data.type" @error="onError" />
    </video>
    <div v-else class="d-flex align-items-center rounded border p-3">
      <BButton variant="light" size="lg" class="fa-2x me-3" @click="startDownload">
        <VespFa icon="cloud-arrow-down" class="fa-fw" />
      </BButton>
      <div class="d-flex flex-column">
        <div class="fw-medium text-break">{{ block.data.title }}</div>
        <div class="small text-muted">{{ description }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type {OutputBlockData} from '@editorjs/editorjs'
import {useElementVisibility} from '@vueuse/core'
import {getDescription} from '~/components/editor/blocks/file'

const props = defineProps({
  block: {
    type: Object as PropType<OutputBlockData>,
    required: true,
  },
  maxWidth: {
    type: Number,
    default: 800,
  },
})

const description = computed(() => {
  return getDescription(props.block.data)
})

const video = ref()
const isVideo = ref<boolean>(props.block?.data?.type?.startsWith('video/') || false)
const videoIsVisible = useElementVisibility(video)
const videoProps = ref({
  preload: 'none',
  controls: true,
  style: {'max-width': props.maxWidth + 'px'},
  onError,
})

function startDownload() {
  window.location.href = getFileLink(props.block.data)
}

function onError() {
  isVideo.value = false
}

watch(videoIsVisible, (newValue) => {
  if (newValue) {
    videoProps.value.preload = 'auto'
  }
})
</script>
