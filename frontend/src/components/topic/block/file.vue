<template>
  <div class="d-flex align-items-center rounded border p-3">
    <b-button variant="light" size="lg" class="fa-2x me-3" @click="startDownload">
      <fa icon="cloud-arrow-down" class="fa-fw" />
    </b-button>
    <div class="d-flex flex-column">
      <div class="fw-medium text-break">
        {{ block.data.title }}
      </div>
      <div class="small text-muted">
        {{ description }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type {OutputBlockData} from '@editorjs/editorjs'
import {getDescription} from '~/components/editor/blocks/file'

const props = defineProps({
  block: {
    type: Object as PropType<OutputBlockData>,
    required: true,
  },
})

const description = computed(() => {
  return getDescription(props.block.data)
})

function startDownload() {
  window.location.href = getFileLink(props.block.data)
}
</script>
