<template>
  <div :class="type + '-content'">
    <template v-for="block in content.blocks" :key="block.id">
      <player-video v-if="block.type === 'video'" :uuid="block.data.uuid" />
      <player-embed v-else-if="block.type === 'embed'" :url="block.data.url" />
      <topic-block-paragraph v-else-if="block.type === 'paragraph'" :block="block" />
      <topic-block-file v-else-if="block.type === 'file'" :block="block" />
      <topic-block-image v-else-if="block.type === 'image'" :block="block" :max-width="type === 'topic' ? 800 : 400" />
      <topic-block-audio v-else-if="block.type === 'audio'" :block="block" />
      <topic-block-code v-else-if="block.type === 'code'" :block="block" />
      <topic-block-header v-else-if="block.type === 'header'" :block="block" />
      <topic-block-list v-else-if="block.type === 'list'" :block="block" />
      <template v-else>{{ block }}</template>
    </template>
  </div>
</template>

<script setup lang="ts">
defineProps({
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
</script>
