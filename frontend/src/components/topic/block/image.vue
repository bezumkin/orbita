<template>
  <b-img v-bind="imageProps" fluid rounded lazy />
</template>

<script setup lang="ts">
import type {OutputBlockData} from '@editorjs/editorjs'

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

const {$image, $lightbox, $isMobile} = useNuxtApp()
const {width, height} = props.block.data
const delimiter = width / height
const w = Math.min(width, $isMobile.value ? props.maxWidth / 2 : props.maxWidth)
const h = Math.round(Math.min(w / delimiter, w / 1.7777777778))
const fit = 'crop'

const imageProps = computed(() => {
  const data: Record<string, any> = {
    src: $image(props.block.data, {w, h, fit}),
    width: w,
    height: h,
  }

  if (width > w * 1.25 || height > h * 1.25) {
    if (width >= w * 2 || height >= h * 2) {
      data.srcSet = $image(props.block.data, {w: w * 2, h: h * 2, fit}) + ' 2x'
    }
    data.style = {cursor: 'pointer'}
    data.onClick = () => {
      $lightbox({elements: [{href: $image(props.block.data), type: 'image'}]}).open()
    }
  }

  return data
})
</script>
