<template>
  <audio ref="audio" :title="title" preload="metadata" />
</template>

<script setup lang="ts">
const props = defineProps({
  uuid: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    default: '',
  },
})

const audio = ref()
const player = ref()

onMounted(() => {
  player.value = initAudioPlayer(props.uuid, audio.value)
})

onUnmounted(() => {
  if (player.value && 'destroy' in player.value) {
    player.value.destroy()
  }
})
</script>
