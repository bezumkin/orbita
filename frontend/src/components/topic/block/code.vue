<template>
  <pre class="highlighted"><code v-html="$prism(block.data.code, block.data.language)" /></pre>
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
