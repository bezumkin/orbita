<template>
  <div class="d-flex flex-column gap-1">
    <div v-for="block in content.blocks" :key="block.id">
      <div v-if="block.type === 'paragraph'" @click="onTextClick" v-html="block.data.text" />
      <player-video v-else-if="block.type === 'video'" :uuid="block.data.uuid" />
      <player-embed v-else-if="block.type === 'embed'" :url="block.data.url" />
      <topic-block-file v-else-if="block.type === 'file'" :block="block" />
      <topic-block-image v-else-if="block.type === 'image'" :block="block" />
      <topic-block-audio v-else-if="block.type === 'audio'" :block="block" />
      <topic-block-code v-else-if="block.type === 'code'" :block="block" />
      <template v-else>{{ block }}</template>
    </div>
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

function onTextClick(e: MouseEvent) {
  const target = e.target as HTMLLinkElement
  // External link
  if (target.tagName === 'A' && /:\/\//.test(target.href)) {
    e.preventDefault()
    window.open(target.href)
  }
}
</script>
