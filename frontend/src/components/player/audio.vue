<template>
  <audio v-show="false" ref="audio" :src="source" controls preload="metadata" />
</template>

<script setup lang="ts">
const props = defineProps({
  uuid: {
    type: String,
    required: true,
  },
})

const {$plyr} = useNuxtApp()
const audio = ref()
const player = ref()
const source = getApiUrl() + 'audio/' + props.uuid

onMounted(() => {
  $plyr(audio.value)
})

onUnmounted(() => {
  if (player.value) {
    player.value.destroy()
  }
})
</script>
