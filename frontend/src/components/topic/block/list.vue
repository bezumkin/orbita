<template>
  <component :is="block.data.style === 'ordered' ? 'ol' : 'ul'" @click="$contentClick">
    <li v-for="(item, idx) in block.data.items" :key="idx" v-html="item" />
  </component>
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
