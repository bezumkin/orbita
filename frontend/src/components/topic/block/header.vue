<template>
  <component :is="'h' + block.data.level" @click="$contentClick" v-html="block.data.text"></component>
</template>

<script setup lang="ts">
import type {OutputBlockData} from '@editorjs/editorjs'

defineProps({
  block: {
    type: Object as PropType<OutputBlockData>,
    required: true,
  },
})
</script>
