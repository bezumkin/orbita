<template>
  <div class="py-3 border rounded">
    <div class="px-4 fw-medium text-break">{{ block.data.title }}</div>
    <div class="px-2">
      <player-audio :uuid="block.data.uuid" />
    </div>
  </div>
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
