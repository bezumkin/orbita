<template>
  <div>
    <file-queue accept="video/mp4" @success="onSuccess" />

    <vesp-table ref="table" class="mt-4" v-bind="{url, filters, fields, tableActions, rowClass, sort, dir}">
      <template #cell(image)="{value}: any">
        <b-img
          v-if="value?.id"
          :src="$image(value, {w: 100, h: 50, fit: 'crop'})"
          :srcset="$image(value, {w: 200, h: 100, fit: 'crop'}) + ' 2x'"
          width="100"
          height="50"
          class="rounded"
        />
      </template>
      <template #cell(title)="{item, value}">
        <div>{{ value }}</div>
        <div class="small text-muted">{{ item.id }}</div>
      </template>
      <template #cell(progress)="{item, value}">
        <div>{{ value !== null ? value + '%' : '' }}</div>
        <div class="small text-muted">{{ formatDate(item.processed_at) }}</div>
      </template>
      <template #cell(dimension)="{item}">
        <div v-if="item.width && item.height">{{ item.width }}x{{ item.height }}</div>
      </template>
      <nuxt-page />
    </vesp-table>
  </div>
</template>

<script setup lang="ts">
import prettyBytes from 'pretty-bytes'
import type {VespTableAction} from '~/components/vesp/table.vue'

const {t, d} = useI18n()
const {$socket} = useNuxtApp()
const url = 'admin/videos'
const sort = 'created_at'
const dir = 'desc'
const table = ref()
const filters = ref({query: ''})
const fields = computed(() => [
  // {key: 'id', label: t('models.video.id'), sortable: true},
  {key: 'image', label: t('models.video.image')},
  {key: 'title', label: t('models.video.title'), sortable: true},
  {key: 'progress', label: t('models.video.progress'), sortable: true},
  {key: 'file.size', label: t('models.video.size'), sortable: true, formatter: formatSize},
  {key: 'file.width', label: t('models.video.dimension'), formatter: formatDimension},
  {key: 'processed_qualities', label: t('models.video.qualities'), formatter: formatQualities},
  {key: 'created_at', label: t('models.video.created_at'), sortable: true, formatter: formatDate},
])
const tableActions: ComputedRef<VespTableAction[]> = computed(() => [
  {
    route: {name: 'admin-videos-id-view'},
    icon: 'play',
    title: t('actions.view'),
    isActive: (item: any) => item && item.processed_qualities.length > 0,
  },
  {
    route: {name: 'admin-videos-id-edit'},
    icon: 'edit',
    title: t('actions.edit'),
    isActive: (item: any) => item && item.processed !== false,
  },
  {
    route: {name: 'admin-videos-id-error'},
    icon: 'question',
    title: t('actions.view'),
    isActive: (item: any) => item && item.processed === false,
  },
  {function: (i: any) => table.value.delete(i), icon: 'times', title: t('actions.delete'), variant: 'danger'},
])

function onSuccess() {
  table.value.refresh()
}
function formatSize(value: any) {
  return value ? prettyBytes(value) : ''
}
function formatDate(value: any) {
  return value ? d(value, 'long') : ''
}

function formatDimension(_value: any, _field: any, item: any) {
  if (!item || !item.file) {
    return ''
  }

  return item.file.width && item.file.height ? [item.file.width, item.file.height].join('x') : ''
}

function formatQualities(value: any) {
  return value ? value.map((i: number) => i + 'p').join(', ') : ''
}

function rowClass(item: any) {
  if (item) {
    const cls = []
    if (item.processed === false) {
      cls.push('error')
    }
    if (!item.active) {
      cls.push('inactive')
    }
    return cls
  }
}

function listener(data: any) {
  if (table.value && table.value.items) {
    const item = table.value.items.find((i: any) => i.id === data.id)
    if (item) {
      const keys = Object.keys(data)
      Object.keys(item).forEach((key: string) => {
        if (keys.includes(key)) {
          item[key] = data[key]
        }
      })
    }
  }
}

onMounted(() => {
  $socket.on('transcode', listener)
})

onUnmounted(() => {
  $socket.off('transcode', listener)
})
</script>
