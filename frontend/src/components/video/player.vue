<template>
  <video ref="video" controls :poster="poster" class="mw-100" v-on="listeners">
    <source :src="source" type="application/x-mpegURL" />
  </video>
</template>

<script setup lang="ts">
import Hls from 'hls.js'
import Plyr from 'plyr'

const {user} = useAuth()
const props = defineProps({
  uuid: {
    type: String,
    required: true,
  },
  status: {
    type: Object,
    default: undefined,
  },
})

const video = ref()
const hls = ref()
const player = ref()

let ready = false
const source = getApiUrl() + 'video/' + props.uuid
const poster = getApiUrl() + 'poster/' + props.uuid
const currentLevel = ref(0)
const levels: Ref<number[]> = computed(() => {
  return hls.value && hls.value.levels ? hls.value.levels.map((i: Record<string, any>) => i.height) : []
})
function selectLevel(size: number) {
  const idx = levels.value.findIndex((i) => i === size)
  if (idx !== -1) {
    currentLevel.value = hls.value.currentLevel = idx
    saveStatus()
  }
}

function initPlayer() {
  if (!props.uuid) {
    return
  }

  hls.value = new Hls()

  hls.value.loadSource(source)
  hls.value.attachMedia(video.value)
  // eslint-disable-next-line import/no-named-as-default-member
  hls.value.on(Hls.Events.MANIFEST_PARSED, () => {
    const options: Record<string, any> = {
      keyboard: {
        focused: true,
        global: true,
      },
      storage: {
        enabled: !user,
        key: 'plyr',
      },
      volume: props.status && props.status.volume !== undefined ? props.status.volume : 1,
      speed: {
        selected: props.status && props.status.speed ? props.status.speed : 1,
        options: [0.25, 0.5, 0.75, 1, 1.25, 1.5, 1.75, 2, 2.5, 3],
      },
    }
    if (levels.value.length) {
      options.quality = {
        default: props.status && props.status.quality ? props.status.quality : levels.value[0],
        options: levels.value,
        forced: true,
        onChange: selectLevel,
      }
    }

    player.value = new Plyr(video.value, options)
  })
}

onMounted(initPlayer)

onUnmounted(() => {
  if (player.value) {
    player.value.destroy()
  }
  if (hls.value) {
    hls.value.destroy()
  }
})

function setStatus() {
  if (!ready && props.status) {
    if (props.status.quality) {
      selectLevel(props.status.quality)
    }
    if (props.status.time) {
      player.value.currentTime = props.status.time
    }
    if (props.status.speed) {
      player.value.speed = props.status.speed
    }
    if (props.status.volume !== undefined) {
      player.value.volume = props.status.volume
    }
  }
  ready = true
}

async function saveStatus(_e?: Event) {
  if (user && ready) {
    const params = {
      time: Math.round(player.value.currentTime),
      quality: levels.value[currentLevel.value],
      speed: player.value.speed,
      volume: player.value.volume,
    }
    const canPost =
      !props.status ||
      props.status.time !== params.time ||
      props.status.quality !== params.quality ||
      props.status.speed !== params.speed ||
      props.status.volume !== params.volume
    if (canPost) {
      await usePost(source, params)
    }
  }
}

let initialTime = Number(new Date())
function timeUpdate(e: Event) {
  const currentTime = Number(new Date())
  if (currentTime - initialTime >= 10000) {
    initialTime = currentTime
    saveStatus(e)
  }
}

const listeners = {
  pause: saveStatus,
  ratechange: saveStatus,
  seeked: saveStatus,
  timeupdate: timeUpdate,
  ended: saveStatus,
  // volumechange: saveStatus,
  canplay: setStatus,
}
</script>
