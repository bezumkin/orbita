<template>
  <BImg v-bind="imageProps" fluid rounded lazy />
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
const {width, height, crop} = props.block.data
const delimiter = width / height
const w = crop && crop.width > 0 ? crop.width : Math.min(width, $isMobile.value ? props.maxWidth / 2 : props.maxWidth)
const h = crop && crop.height > 0 ? crop.height : Math.round(Math.min(w / delimiter, w / 1.7777777778))
const fit = crop && crop.fit ? crop.fit : 'crop'

const imageProps = computed(() => {
  const data: Record<string, any> = {
    src: $image(props.block.data, {w, h, fit}),
    width: w,
    height: h,
    'data-url': $image(props.block.data),
  }

  if (width > w * 1.25 || height > h * 1.25) {
    if (width >= w * 2 || height >= h * 2) {
      data.srcSet = $image(props.block.data, {w: w * 2, h: h * 2, fit}) + ' 2x'
    }
    data.style = {cursor: 'pointer'}

    data.onClick = (e: MouseEvent) => {
      let startAt = 0
      const elements: any[] = []
      const target = e.target as HTMLImageElement

      if (target.parentElement?.classList.contains('images-group')) {
        target.parentElement?.querySelectorAll('img').forEach((img: HTMLImageElement, idx: number) => {
          elements.push({href: img.dataset.url, type: 'image'})
          if (img.dataset.url === data['data-url']) {
            startAt = idx
          }
        })
      } else {
        elements.push({href: data['data-url'], type: 'image'})
      }

      $lightbox({elements, startAt}).open()
    }
  }

  return data
})
</script>
