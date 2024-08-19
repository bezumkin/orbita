<template>
  <video ref="video" />
</template>

<script setup lang="ts">
const props = defineProps({
  uuid: {
    type: String,
    required: true,
  },
  autoplay: {
    type: Boolean,
    default: false,
  },
  poster: {
    type: String,
    default: undefined,
  },
})

const video = ref()
const player = ref()

async function initPlayer() {
  if (!props.uuid) {
    return
  }

  player.value = await initVideoPlayer(props.uuid, video.value, {
    poster: props.poster,
    autoPlay: props.autoplay,
  })
}

onMounted(() => {
  if (props.autoplay) {
    initPlayer()
  }
})

onUnmounted(() => {
  if (player.value && 'destroy' in player.value) {
    player.value.destroy()
  }
})
</script>
