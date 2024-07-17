<template>
  <div class="paragraph" @click="$contentClick" v-html="html" />
</template>

<script setup lang="ts">
import type {OutputBlockData} from '@editorjs/editorjs'

const props = defineProps({
  block: {
    type: Object as PropType<OutputBlockData>,
    required: true,
  },
})

const html = computed(() => {
  if (!props.block.data?.text) {
    return ''
  }

  const text = props.block.data.text

  const quote = /^(&gt;|>)(&nbsp;|\s)/
  if (text.match(quote)) {
    return '<blockquote>' + text.replace(quote, '').replace(/<.*?>/g, '') + '</blockquote>'
  }

  if (text === '-') {
    return '<div class="my-1"></div>'
  }

  if (text === '--') {
    return '<div class="my-2"></div>'
  }

  if (text === '---') {
    return '<hr class="my-3" />'
  }

  return text
})
</script>
