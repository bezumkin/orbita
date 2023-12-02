<template>
  <h2 v-if="block.data.level === 2" @click="$contentClick" v-html="block.data.text" />
  <h3 v-else-if="block.data.level === 3" @click="$contentClick" v-html="block.data.text" />
  <h4 v-else-if="block.data.level === 4" @click="$contentClick" v-html="block.data.text" />
  <h5 v-else-if="block.data.level === 5" @click="$contentClick" v-html="block.data.text" />
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
