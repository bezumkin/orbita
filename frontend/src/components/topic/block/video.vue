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

  <template v-if="useAudio || useDownload">
    <div v-if="useAudio && playing === 'audio'">
      <PlayerAudio v-bind="audioProps" />
    </div>
    <div v-else class="d-flex flex-wrap justify-content-center">
      <BButton v-if="useAudio" variant="link" @click="playAudio">
        <VespFa icon="play" fixed-width />
        {{ t('models.video.play.audio') }}
      </BButton>
      <BButton v-if="useDownload" variant="link" :href="downloadVideo">
        <VespFa icon="download" fixed-width />
        {{ t('models.video.download.video', {size: videoSize}) }}
      </BButton>
    </div>

    <div v-if="playing === 'audio'" class="d-flex flex-wrap justify-content-center">
      <BButton variant="link" @click="playVideo">
        <VespFa icon="play" fixed-width />
        {{ t('models.video.play.video') }}
      </BButton>
      <BButton v-if="useDownload" variant="link" :href="downloadAudio">
        <VespFa icon="download" fixed-width />
        {{ t('models.video.download.audio', {size: audioSize}) }}
      </BButton>
    </div>
  </template>
</template>

<script setup lang="ts">
import type {OutputBlockData} from '@editorjs/editorjs'
import prettyBytes from 'pretty-bytes'
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
const {$variables} = useNuxtApp()
const useAudio = Number($variables.value.EXTRACT_VIDEO_AUDIO_ENABLED) && props.block.data.audio
const useDownload = $variables.value.DOWNLOAD_MEDIA_ENABLED === '1' && props.block.data.moved !== false

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

const api = getApiUrl()
const downloadVideo = api + 'video/' + videoProps.value.uuid + '/download'
const downloadAudio = api + 'audio/' + audioProps.value.uuid
const videoSize = props.block.data.size ? prettyBytes(props.block.data.size) : 0
const audioSize = props.block.data.audio_size ? prettyBytes(props.block.data.audio_size) : 0

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
