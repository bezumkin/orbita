<template>
  <vesp-modal ref="modal" :title="$t('actions.editor.pick_video')" @shown="onShown" @hidden="onHidden">
    <b-form-group>
      <b-input-group>
        <template #append>
          <b-button :disabled="!query" @click="onQueryClear">
            <fa icon="times" />
          </b-button>
        </template>
        <b-form-input ref="input" v-model="query" :placeholder="$t('components.table.query')" />
      </b-input-group>
    </b-form-group>

    <b-overlay v-if="videos.length" :show="loading" opacity="0.5">
      <div class="grid">
        <b-card v-for="video in videos" :key="video.id" v-bind="getProps(video)" @click="pick(video)">
          <template #img>
            <b-img v-if="video.image" :src="$image(video.image, {w: 200})" />
          </template>
          <template #default>
            <div class="fw-medium">{{ video.title }}</div>
            <b-card-text class="small mt-auto pt-2 d-flex flex-column gap-1">
              <div v-if="video.created_at">{{ d(video.created_at, 'long') }}</div>
              <div v-if="video.processed_qualities">
                {{ video.processed_qualities.map((i: number) => i + 'p').join(', ') }}
              </div>
            </b-card-text>
          </template>
        </b-card>
      </div>
      <b-pagination
        v-if="total > limit"
        v-model="page"
        :total-rows="total"
        :per-page="limit"
        :limit="5"
        :hide-goto-end-buttons="true"
        align="center"
        class="mt-4"
      />
    </b-overlay>
    <b-alert v-else-if="!loading" variant="light" :model-value="true">
      {{ query ? $t('components.table.no_results') : $t('components.table.no_data') }}
    </b-alert>

    <template #footer="{hide}">
      <b-button variant="secondary" @click="hide">{{ $t('actions.close') }}</b-button>
      <b-button variant="primary" :disabled="!picked" @click="useVideo">{{ $t('actions.pick') }}</b-button>
    </template>
  </vesp-modal>
</template>

<script setup lang="ts">
const {d} = useI18n()
const loading = ref(false)
const videos: Ref<VespVideo[]> = ref([])
const picked: Ref<VespVideo | undefined> = ref()
const query = ref('')
const modal = ref()
const input = ref()

const total = ref(0)
const page = ref(1)
const limit = 4

async function fetch() {
  loading.value = true
  picked.value = undefined
  try {
    const params: Record<string, any> = {active: true, sort: 'created_at', dir: 'desc', limit}
    if (page.value > 1) {
      params.page = page.value
    }
    if (query.value) {
      params.query = query.value
    }
    const data = await useGet('admin/videos', params)
    videos.value = data.rows
    total.value = data.total
  } catch (e) {
  } finally {
    loading.value = false
  }
}

await fetch()

const emit = defineEmits(['hidden'])
function onHidden() {
  emit('hidden')
}
function onShown() {
  if (input.value) {
    input.value.$el.focus()
  }
}
function onQueryClear() {
  query.value = ''
  nextTick(() => {
    onShown()
  })
}

function pick(video: VespVideo) {
  if (picked.value && picked.value.id === video.id) {
    useVideo()
  } else {
    picked.value = video
  }
}

function getProps(video: VespVideo) {
  const isActive = picked.value && picked.value.id === video.id
  return {
    class: 'g-col-12 g-col-md-6 ',
    style: 'cursor: pointer',
    'body-class': 'd-flex flex-column',
    'bg-variant': isActive ? 'white' : 'light',
    'border-variant': isActive ? 'primary' : 'secondary',
  }
}

const pickVideo: Function | undefined = inject('pickVideo')
function useVideo() {
  if (pickVideo && picked.value) {
    pickVideo(picked.value)
    modal.value.hide()
  }
}

watch(page, fetch)
watch(query, () => {
  if (page.value > 1) {
    page.value = 1
  } else {
    fetch()
  }
})
</script>
