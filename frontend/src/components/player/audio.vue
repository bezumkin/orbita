<template>
  <audio ref="audio" :src="source" controls preload="metadata" class="mw-100" />
</template>

<script setup lang="ts">
import Plyr from 'plyr'

const props = defineProps({
  uuid: {
    type: String,
    required: true,
  },
})

const audio = ref()
const player = ref()

const source = getApiUrl() + 'audio/' + props.uuid
player.value = new Plyr(audio.value)
</script>
