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
})

const {$image, $lightbox, $isMobile} = useNuxtApp()
const {width} = props.block.data
const maxWidth = computed(() => ($isMobile.value ? 450 : 850))

const imageProps = computed(() => {
  const data: Record<string, any> = {}
  if (width > maxWidth.value) {
    data.src = $image(props.block.data, {w: maxWidth.value, fit: 'crop'})
    if (width >= maxWidth.value * 2) {
      data.srcSet = $image(props.block.data, {w: maxWidth.value * 2, fit: 'crop'})
    }
    data.onClick = () => {
      $lightbox({
        elements: [{href: $image(props.block.data), type: 'image'}],
      }).open()
    }
    data.style = {cursor: 'pointer'}
  } else {
    data.src = $image(props.block.data)
  }

  return data
})
</script>
