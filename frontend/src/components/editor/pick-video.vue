<template>
  <VespModal ref="modal" :title="$t('actions.editor.pick_video')" size="lg" @shown="onShown" @hidden="onHidden">
    <BFormGroup>
      <BInputGroup>
        <template #append>
          <BButton :disabled="!query" @click="onQueryClear">
            <VespFa icon="times" />
          </BButton>
        </template>
        <BFormInput ref="input" v-model="query" :placeholder="$t('components.table.query')" />
      </BInputGroup>
    </BFormGroup>

    <div class="grid">
      <BCard v-for="video in videos" :key="video.id" v-bind="getProps(video)" @click="pick(video)">
        <template #img>
          <BImg v-if="video.image" :src="$image(video.image, {w: 1024})" class="border-bottom" />
        </template>
        <template #default>
          <div class="fw-medium">{{ video.title }}</div>
          <BCardText class="small mt-auto pt-2 d-flex flex-column gap-1">
            <div v-if="video.created_at">{{ d(video.created_at, 'long') }}</div>
            <div v-if="video.processed_qualities">
              {{ video.processed_qualities.map((i: number) => i + 'p').join(', ') }}
            </div>
          </BCardText>
        </template>
      </BCard>
    </div>
    <BPagination
      v-if="total > limit"
      v-model="page"
      :total-rows="total"
      :per-page="limit"
      :limit="5"
      :hide-goto-end-buttons="true"
      align="center"
      class="mt-4"
    />
    <div v-if="!videos.length && !loading" class="alert alert-light">
      {{ query ? $t('errors.video.pick_not_found') : $t('errors.video.pick_none') }}
    </div>

    <template #footer="{hide}">
      <BButton variant="secondary" @click="hide">{{ $t('actions.close') }}</BButton>
      <BButton variant="primary" :disabled="!picked" @click="useVideo">{{ $t('actions.pick') }}</BButton>
    </template>
  </VespModal>
</template>

<script setup lang="ts">
const {d} = useI18n()

const picked: Ref<VespVideo | undefined> = ref()
const query = ref('')
const modal = ref()
const input = ref()
const loading = ref(false)
const videos: Ref<VespVideo[]> = ref([])
const total = ref(0)
const page = ref(1)
const limit = 6

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
    class: 'g-col-12 g-col-md-6 g-col-lg-4 overflow-hidden',
    'body-class': 'd-flex flex-column',
    style: {
      cursor: 'pointer',
      background: isActive ? 'var(--bs-secondary-bg)' : 'var(--bs-tertiary-bg)',
      'border-color': isActive ? 'var(--bs-primary)' : '',
    },
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
