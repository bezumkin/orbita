<template>
  <PlayerAudio :uuid="block.data.uuid" :title="block.data.title" />
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
