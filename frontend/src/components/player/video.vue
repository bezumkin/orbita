<template>
  <video ref="video" :src="sourceUrl" controls :poster="poster" v-on="listeners" />
</template>

<script setup lang="ts">
import Hls from 'hls.js'

const {user} = useAuth()
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
})

const video = ref()
const hls = ref()
const player = ref()
const status = ref()

let ready = false
const {$plyr} = useNuxtApp()
const sourceUrl = getApiUrl() + 'video/' + props.uuid
const chaptersUrl = getApiUrl() + 'video/' + props.uuid + '/chapters'
const statusUrl = getApiUrl() + 'user/video/' + props.uuid
const currentLevel = ref(0)
const levels = computed<number[]>(() => {
  return hls.value && hls.value.levels ? hls.value.levels.map((i: Record<string, any>) => i.height) : []
})
const chapters = ref<null | Record<string, string>>(null)

function selectLevel(size: number) {
  if (size < 0) {
    currentLevel.value = hls.value.currentLevel = -1
  } else {
    const idx = levels.value.findIndex((i) => i === size)
    if (idx !== -1) {
      currentLevel.value = hls.value.currentLevel = idx
      saveStatus()
    }
  }
}

function initPlayer() {
  if (!props.uuid) {
    return
  }

  hls.value = new Hls()
  hls.value.loadSource(sourceUrl)
  hls.value.attachMedia(video.value)

  // eslint-disable-next-line import/no-named-as-default-member
  hls.value.on(Hls.Events.LEVEL_SWITCHED, function (_e: string, data: Record<string, any>) {
    const span = document.querySelector('.plyr__menu__container [data-plyr="quality"][value="-1"] span')
    if (span) {
      if (hls.value.autoLevelEnabled) {
        span.innerHTML = `Auto (${hls.value.levels[data.level].height}p)`
      } else {
        span.innerHTML = 'Auto'
      }
    }
  })

  // eslint-disable-next-line import/no-named-as-default-member
  hls.value.on(Hls.Events.MANIFEST_PARSED, async () => {
    await Promise.all([loadStatus(), loadChapters()])
    const options: Record<string, any> = {
      keyboard: {
        focused: true,
        global: true,
      },
      storage: {
        enabled: !user.value,
        key: 'plyr',
      },
      volume: status.value && status.value.volume !== undefined ? status.value.volume : 1,
      speed: {
        selected: status.value && status.value.speed ? status.value.speed : 1,
        options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2],
      },
    }
    if (levels.value.length) {
      options.quality = {
        default: status.value && status.value.quality ? status.value.quality : -1,
        options: [-1, ...levels.value],
        forced: true,
        onChange: selectLevel,
      }
    }
    if (chapters.value) {
      options.markers = {
        enabled: true,
        points: [],
      }
      Object.keys(chapters.value).forEach((time: string) => {
        options.markers.points.push({
          // @ts-ignore
          time: time.split(':').reduce((a, b) => Number(a) * 60 + Number(b)),
          label: chapters.value ? chapters.value[time] : '',
        })
      })
    }

    player.value = $plyr(video.value, options)
    player.value.play()
  })
}

onMounted(() => {
  if (props.autoplay) {
    initPlayer()
  }
})

onUnmounted(() => {
  saveStatus()
  if (player.value) {
    player.value.destroy()
  }
  if (hls.value) {
    hls.value.destroy()
  }
})

async function loadStatus() {
  try {
    if (user.value) {
      status.value = await useGet(statusUrl)
    }
  } catch (e) {}
}

async function loadChapters() {
  try {
    chapters.value = await useGet(chaptersUrl)
  } catch (e) {}
}

function setStatus() {
  if (!ready && status.value) {
    if (status.value.quality) {
      selectLevel(status.value.quality)
    }
    if (status.value.time) {
      player.value.currentTime = status.value.time
    }
    if (status.value.speed) {
      player.value.speed = status.value.speed
    }
    if (status.value.volume !== undefined) {
      player.value.volume = status.value.volume
    }
  }
  ready = true
}

async function saveStatus(_e?: Event) {
  if (user.value && ready && hls.value?.currentLevel) {
    const params = {
      time: Math.round(player.value.currentTime),
      quality: levels.value[hls.value.currentLevel],
      speed: player.value.speed,
      volume: player.value.volume,
    }
    const canPost =
      !status.value ||
      status.value.time !== params.time ||
      status.value.quality !== params.quality ||
      status.value.speed !== params.speed ||
      status.value.volume !== params.volume
    if (canPost) {
      await usePost(statusUrl, params)
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
