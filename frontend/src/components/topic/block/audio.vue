<template>
  <PlayerAudio :uuid="block.data.uuid" :title="block.data.title" />

  <div v-if="useDownload">
    <div class="text-center">
      <BButton variant="link" :href="downloadLink">
        <VespFa icon="download" />
        {{ $t('models.file.download', {size}) }}
      </BButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import type {OutputBlockData} from '@editorjs/editorjs'
import prettyBytes from 'pretty-bytes'

const props = defineProps({
  block: {
    type: Object as PropType<OutputBlockData>,
    required: true,
  },
})

const {$variables} = useNuxtApp()
const useDownload = $variables.value.DOWNLOAD_MEDIA_ENABLED === '1'
const downloadLink = getApiUrl() + 'audio/' + props.block.data.uuid
const size = props.block.data.size ? prettyBytes(props.block.data.size) : 0
</script>
