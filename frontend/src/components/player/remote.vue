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
})

const {$plyr} = useNuxtApp()
const frame = ref()
const player = ref()

function initPlayer() {
  if (!props.url) {
    return
  }

  player.value = $plyr(frame.value)
  player.value.play()
}

onMounted(initPlayer)

onUnmounted(() => {
  if (player.value) {
    player.value.destroy()
  }
})
</script>
