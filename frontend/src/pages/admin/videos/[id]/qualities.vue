<template>
  <VespModal :title="record.title" size="lg" :cancel-title="$t('actions.close')">
    <div class="vesp-table">
      <BTable ref="table" :fields="fields" :items="qualities" responsive>
        <template #cell(progress)="{item, value}">
          <div class="d-flex flex-column gap-2 gap-md-1">
            <BRow no-gutters>
              <BCol md="3" class="fw-medium">{{ t('models.video_quality.progress') }}:</BCol>
              <BCol md="8">{{ value !== null ? value + '%' : '' }}</BCol>
            </BRow>
            <BRow no-gutters>
              <BCol md="3" class="fw-medium">{{ t('models.video_quality.created_at') }}:</BCol>
              <BCol md="8">{{ formatDate(item.created_at) }}</BCol>
            </BRow>
            <BRow v-if="item.processed_at" no-gutters>
              <BCol md="3" class="fw-medium">{{ t('models.video_quality.processed_at') }}:</BCol>
              <BCol md="8">
                {{ formatDate(item.processed_at) }}{{ getDateDiff(item.created_at, item.processed_at) }}
              </BCol>
            </BRow>
            <BRow v-if="item.moved_at" no-gutters>
              <BCol md="3" class="fw-medium">{{ t('models.video_quality.moved_at') }}:</BCol>
              <BCol md="8">{{ formatDate(item.moved_at) }}{{ getDateDiff(item.processed_at, item.moved_at) }}</BCol>
            </BRow>
            <BRow v-if="item.file && item.file.size" no-gutters>
              <BCol md="3" class="fw-medium">{{ t('models.video_quality.size') }}:</BCol>
              <BCol md="8">{{ prettyBytes(item.file.size) }}</BCol>
            </BRow>
          </div>
        </template>
      </BTable>
    </div>
  </VespModal>
</template>

<script setup lang="ts">
import {formatDuration, intervalToDuration, parseISO} from 'date-fns'
import prettyBytes from 'pretty-bytes'

const {$socket} = useNuxtApp()
const {t, d} = useI18n()
const locale = useDateLocale()
const fields = computed(() => [
  {key: 'quality', label: t('models.video_quality.quality'), formatter: formatQuality},
  {key: 'progress', label: t('models.video_quality.status')},
])

const url = 'admin/videos/' + useRoute().params.id
const record = ref<VespVideo>({id: ''})
const qualities = ref<VespVideoQuality[]>([])

function formatQuality(value: any) {
  return value ? value + 'p' : ''
}

function formatDate(value: any) {
  return value ? d(value, 'long') : ''
}

try {
  record.value = await useGet(url)
} catch (e: any) {
  showError({statusCode: e.statusCode, statusMessage: e.message})
}

function updateQualities(video: VespVideo) {
  if (video.id === record.value.id && video.qualities) {
    qualities.value = video.qualities
  }
}

async function fetch() {
  try {
    const data = await useGet(url + '/qualities')
    qualities.value = data.rows
  } catch (e) {}
}

function getDateDiff(start: string, end: string) {
  const duration = intervalToDuration({start: parseISO(start), end: parseISO(end)})
  const text = formatDuration(duration, {locale: locale.value, format: ['hours', 'minutes', 'seconds']})
  return text ? ` (${text})` : ''
}

onMounted(() => {
  fetch()
  $socket.on('transcode', updateQualities)
})
</script>
