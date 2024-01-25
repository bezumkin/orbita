<template>
  <div :class="type + '-content'">
    <template v-for="block in blocks" :key="block.id">
      <topic-block-paragraph v-if="block.type === 'paragraph'" :block="block" />
      <topic-block-header v-else-if="block.type === 'header'" :block="block" />
      <topic-block-file v-else-if="block.type === 'file'" :block="block" />
      <topic-block-image v-else-if="block.type === 'image'" :block="block" :max-width="maxWidth" />
      <topic-block-video v-else-if="block.type === 'video'" :block="block" :max-width="maxWidth" />
      <topic-block-embed v-else-if="block.type === 'embed'" :block="block" :max-width="maxWidth" />
      <topic-block-audio v-else-if="block.type === 'audio'" :block="block" />
      <topic-block-code v-else-if="block.type === 'code'" :block="block" />
      <topic-block-list v-else-if="block.type === 'list'" :block="block" />
      <div v-else-if="block.type === 'images'" class="images-group d-flex flex-wrap gap-2">
        <topic-block-image v-for="image in block.data" :key="image.id" :block="image" :max-width="maxWidth" />
      </div>
      <div v-else>{{ block }}</div>
    </template>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  content: {
    type: Object as PropType<Record<string, any>>,
    default() {
      return {
        blocks: [],
      }
    },
  },
  type: {
    type: String,
    default: 'topic',
  },
})

const maxWidth = computed(() => (props.type === 'topic' ? 800 : 400))

const blocks = computed(() => {
  const items = []
  let images: any[] = []
  props.content.blocks.forEach((item: any) => {
    if (item.type === 'image') {
      images.push(item)
    } else {
      if (images.length > 1) {
        items.push({type: 'images', data: images, id: Math.random().toString(36).slice(2, 10)})
        images = []
      } else if (images.length) {
        items.push(images.shift())
      }
      items.push(item)
    }
  })
  if (images.length > 1) {
    items.push({type: 'images', data: images, id: Math.random().toString(36).slice(2, 10)})
  } else if (images.length) {
    items.push(images.shift())
  }

  return items
})
</script>
