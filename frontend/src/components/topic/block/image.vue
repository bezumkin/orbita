<template>
  <b-img :src="$image(block.data)" fluid rounded />
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
