<template>
  <vesp-modal ref="modal" :title="$t('actions.editor.pick_video')" size="lg" @shown="onShown" @hidden="onHidden">
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

    <b-overlay :show="loading" opacity="0.5">
      <div class="grid">
        <b-card v-for="video in videos" :key="video.id" v-bind="getProps(video)" @click="pick(video)">
          <template #img>
            <b-img v-if="video.image" :src="$image(video.image, {w: 1024})" />
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
    <b-alert v-if="!videos.length && !loading" variant="light" :model-value="true">
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

const picked: Ref<VespVideo | undefined> = ref()
const query = ref('')
const modal = ref()
const input = ref()

const page = ref(1)
const limit = 6

const params = computed(() => {
  const tmp: Record<string, any> = {active: true, sort: 'created_at', dir: 'desc', limit}
  if (page.value > 1) {
    tmp.page = page.value
  }
  if (query.value) {
    tmp.query = query.value
  }
  return tmp
})

const {data, pending: loading, refresh} = useGet('admin/videos', params)
const videos: ComputedRef<VespVideo[]> = computed(() => data.value?.rows || [])
const total = computed(() => data.value?.total || 0)

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
    class: 'g-col-12 g-col-md-6 g-col-lg-4',
    style: 'cursor: pointer',
    'body-class': 'd-flex flex-column',
    'bg-variant': isActive ? 'white' : 'light',
    'border-variant': isActive ? 'primary' : 'light',
  }
}

const pickVideo: Function | undefined = inject('pickVideo')
function useVideo() {
  if (pickVideo && picked.value) {
    pickVideo(picked.value)
    modal.value.hide()
  }
}

watch(page, () => refresh())
watch(query, () => {
  if (page.value > 1) {
    page.value = 1
  } else {
    refresh()
  }
})
</script>
