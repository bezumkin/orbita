<template>
  <div v-if="!playing" class="play-video-wrapper">
    <div v-bind="wrapperProps">
      <BImg :src="videoProps.poster" />
    </div>
    <BButton variant="primary" class="play-video-button" @click="playVideo">
      <VespFa icon="play" style="width: 3rem; height: 3rem" fixed-width />
    </BButton>
  </div>
  <div v-else-if="playing === 'video'" v-bind="wrapperProps">
    <PlayerVideo v-bind="videoProps" />
  </div>
  <div v-else class="text-center">
    <BButton variant="primary" @click="playVideo">{{ t('models.video.play.video') }}</BButton>
  </div>

  <div v-if="useAudio">
    <div v-if="playing === 'audio'">
      <PlayerAudio v-bind="audioProps" />
    </div>
    <div v-else class="text-center">
      <BButton variant="primary" @click="playAudio">{{ t('models.video.play.audio') }}</BButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import type {OutputBlockData} from '@editorjs/editorjs'
const {t} = useI18n()

const props = defineProps({
  block: {
    type: Object as PropType<OutputBlockData>,
    required: true,
  },
  maxWidth: {
    type: Number,
    default: 800,
  },
  autoplay: {
    type: Boolean,
    default: false,
  },
})
const useAudio = Number(useRuntimeConfig().public.EXTRACT_VIDEO_AUDIO_ENABLED) && props.block.data.audio

const playing = ref<string | undefined>()
const wrapperProps = computed(() => {
  return {
    class: ['ratio', 'ratio-16x9', 'm-auto', 'rounded'],
    style: {maxWidth: props.maxWidth + 'px'},
  }
})
const videoProps = ref({
  autoplay: true,
  uuid: props.block.data.uuid,
  poster: getApiUrl() + 'poster/' + props.block.data.uuid + '/' + props.maxWidth,
  status: {},
})
const audioProps = ref({
  autoplay: true,
  uuid: props.block.data.audio,
  title: t('models.video.audio'),
  status: {},
})

if (props.autoplay) {
  playing.value = 'video'
}

function playVideo() {
  if (playing.value === 'audio') {
    const storage = JSON.parse(localStorage.getItem('player-audio') || '{}')
    if (audioProps.value.uuid in storage) {
      videoProps.value.status.time = storage[audioProps.value.uuid].time || 0
    }
  }
  playing.value = 'video'
}

function playAudio() {
  if (playing.value === 'video') {
    const storage = JSON.parse(localStorage.getItem('player-video') || '{}')
    if (videoProps.value.uuid in storage) {
      audioProps.value.status.time = storage[videoProps.value.uuid].time || 0
    }
  }
  playing.value = 'audio'
}
</script>
