<template>
  <audio ref="audio" :title="title" preload="metadata" />
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
  title: {
    type: String,
    default: '',
  },
  status: {
    type: Object,
    default() {
      return {}
    },
  },
})

const audio = ref()
const player = ref()

onMounted(() => {
  player.value = initAudioPlayer(
    props.uuid,
    audio.value,
    JSON.parse(
      JSON.stringify({
        title: props.title,
        autoPlay: props.autoplay,
        status: props.status,
      }),
    ),
  )
})

onUnmounted(() => {
  if (player.value && 'destroy' in player.value) {
    player.value.destroy()
  }
})
</script>
