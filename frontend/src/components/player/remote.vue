<template>
  <div ref="frame">
    <iframe :src="url" allowfullscreen class="d-none" />
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  url: {
    type: String,
    required: true,
  },
  autoplay: {
    type: Boolean,
    default: false,
  },
})

const {$plyr} = useNuxtApp()
const frame = ref()
const player = ref()

function initPlayer() {
  if (!props.url) {
    return
  }

  player.value = $plyr(frame.value)
  if (props.autoplay) {
    nextTick(() => {
      player.value.play()
    })
  }
}

onMounted(initPlayer)

onUnmounted(() => {
  if (player.value) {
    player.value.destroy()
  }
})
</script>
