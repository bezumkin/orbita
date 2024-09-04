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
  status: {
    type: Object,
    default() {
      return {}
    },
  },
})

const video = ref()
const player = ref()

async function initPlayer() {
  if (!props.uuid) {
    return
  }

  player.value = await initVideoPlayer(
    props.uuid,
    video.value,
    JSON.parse(
      JSON.stringify({
        poster: props.poster,
        autoPlay: props.autoplay,
        status: props.status,
      }),
    ),
  )
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
