<template>
  <div :class="type + '-content'">
    <template v-for="block in blocks" :key="block.id">
      <TopicBlockParagraph v-if="block.type === 'paragraph'" :block="block" />
      <TopicBlockHeader v-else-if="block.type === 'header'" :block="block" />
      <TopicBlockFile v-else-if="block.type === 'file'" :block="block" />
      <TopicBlockImage v-else-if="block.type === 'image'" :block="block" :max-width="maxWidth" />
      <TopicBlockVideo v-else-if="block.type === 'video'" :block="block" :max-width="maxWidth" />
      <TopicBlockEmbed v-else-if="block.type === 'embed'" :block="block" :max-width="maxWidth" />
      <TopicBlockAudio v-else-if="block.type === 'audio'" :block="block" />
      <TopicBlockCode v-else-if="block.type === 'code'" :block="block" />
      <TopicBlockList v-else-if="block.type === 'list'" :block="block" />
      <div v-else-if="block.type === 'images'" class="images-group d-flex flex-wrap gap-2">
        <TopicBlockImage v-for="image in block.data" :key="image.id" :block="image" :max-width="maxWidth" />
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

const maxWidth = computed(() => (props.type === 'topic' ? 837 : 400))

const blocks = computed(() => {
  const items = []
  let images: any[] = []
  if (props.content.blocks && Array.isArray(props.content.blocks)) {
    for (const item of props.content.blocks) {
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
    }
  }
  if (images.length > 1) {
    items.push({type: 'images', data: images, id: Math.random().toString(36).slice(2, 10)})
  } else if (images.length) {
    items.push(images.shift())
  }

  return items
})
</script>
