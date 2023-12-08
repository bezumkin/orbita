<template>
  <div v-if="!activated" class="plyr plyr--full-ui plyr--video">
    <div v-bind="wrapperProps">
      <b-img :src="posterUrl" />
    </div>
    <button class="plyr__control plyr__control--overlaid" @click.prevent="onActivate">
      <svg aria-hidden="true" focusable="false">
        <use :xlink:href="sprite + '#plyr-play'" />
      </svg>
    </button>
  </div>
  <div v-else v-bind="wrapperProps">
    <player-video :uuid="block.data.uuid" :autoplay="true" :poster="posterUrl" />
  </div>
</template>

<script setup lang="ts">
import type {OutputBlockData} from '@editorjs/editorjs'
import sprite from '~/assets/icons/plyr.svg'

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

const posterUrl = getApiUrl() + 'poster/' + props.block.data.uuid + '/' + props.maxWidth
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
