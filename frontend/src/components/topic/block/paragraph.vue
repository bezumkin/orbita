<template>
  <div @click="$contentClick" v-html="html" />
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
  return props.block.data?.text === '---' ? '<hr class="my-3" />' : props.block.data.text
})
</script>
