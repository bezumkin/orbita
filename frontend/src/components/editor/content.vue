<template>
  <div :class="type + '-content'">
    <template v-for="block in content.blocks" :key="block.id">
      <topic-block-paragraph v-if="block.type === 'paragraph'" :block="block" />
      <topic-block-header v-else-if="block.type === 'header'" :block="block" />
      <topic-block-file v-else-if="block.type === 'file'" :block="block" />
      <topic-block-image v-else-if="block.type === 'image'" :block="block" :max-width="maxWidth" />
      <topic-block-video v-else-if="block.type === 'video'" :block="block" :max-width="maxWidth" />
      <topic-block-embed v-else-if="block.type === 'embed'" :block="block" :max-width="maxWidth" />
      <topic-block-audio v-else-if="block.type === 'audio'" :block="block" />
      <topic-block-code v-else-if="block.type === 'code'" :block="block" />
      <topic-block-list v-else-if="block.type === 'list'" :block="block" />
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
</script>
