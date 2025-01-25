<template>
  <div v-if="!activated" class="play-video-wrapper">
    <div v-bind="wrapperProps">
      <BImg :src="posterUrl" />
    </div>
    <BButton variant="primary" class="play-video-button" @click.prevent="onActivate">
      <VespFa icon="play" style="width: 3rem; height: 3rem" fixed-width />
    </BButton>
  </div>
  <div v-else v-bind="wrapperProps">
    <PlayerEmbed :url="url" :autoplay="true" />
  </div>
</template>

<script setup lang="ts">
import type {OutputBlockData} from '@editorjs/editorjs'

const props = defineProps({
  block: {
    type: Object as PropType<OutputBlockData>,
    required: true,
  },
  maxWidth: {
    type: Number,
    default: 800,
  },
})

const posterUrl = getApiUrl() + 'poster/embed/' + props.block.data.service + '/' + props.block.data.id
const url = computed(() => {
  return getEmbedLink(props.block.data.service, props.block.data.id) || props.block.data.url
})
const activated = ref(false)
const wrapperProps = computed(() => {
  return {
    class: ['ratio', 'ratio-16x9', 'm-auto', 'rounded'],
    style: {maxWidth: props.maxWidth + 'px'},
  }
})

function onActivate() {
  activated.value = true
}
</script>
