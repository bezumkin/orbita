<template>
  <div v-if="!activated" class="plyr plyr--full-ui plyr--video" @click.prevent="onActivate">
    <b-img :src="posterUrl" fluid />
    <button class="plyr__control plyr__control--overlaid">
      <svg aria-hidden="true" focusable="false">
        <use v-bind="{'xlink:href': sprite + '#plyr-play'}" />
      </svg>
    </button>
  </div>
  <video v-else ref="video" :src="sourceUrl" controls :poster="posterUrl" class="mw-100" v-on="listeners" />
</template>

<script setup lang="ts">
import Hls from 'hls.js'
import sprite from '~/assets/icons/plyr.svg'

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
})

const video = ref()
const hls = ref()
const player = ref()
const activated = ref(false)
const status = ref()

function onActivate() {
  activated.value = true
  nextTick(() => {
    initPlayer()
  })
}

let ready = false
const {$plyr} = useNuxtApp()
const sourceUrl = getApiUrl() + 'video/' + props.uuid
const statusUrl = getApiUrl() + 'user/video/' + props.uuid
const posterUrl = getApiUrl() + 'poster/' + props.uuid + '/1024'
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
  hls.value.loadSource(sourceUrl)
  hls.value.attachMedia(video.value)

  // eslint-disable-next-line import/no-named-as-default-member
  hls.value.on(Hls.Events.MANIFEST_PARSED, async () => {
    await loadStatus()
    const options: Record<string, any> = {
      keyboard: {
        focused: true,
        global: true,
      },
      storage: {
        enabled: !user,
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
        default: status.value && status.value.quality ? status.value.quality : levels.value[0],
        options: levels.value,
        forced: true,
        onChange: selectLevel,
      }
    }

    player.value = $plyr(video.value, options)
    player.value.play()
  })
}

onMounted(() => {
  if (props.autoplay) {
    onActivate()
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
  if (user.value && ready) {
    const params = {
      time: Math.round(player.value.currentTime),
      quality: levels.value[currentLevel.value],
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
