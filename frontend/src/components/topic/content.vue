<template>
  <div class="topic content">
    <slot name="header" v-bind="myValue">
      <h1>{{ myValue.title }}</h1>
    </slot>
    <div v-if="myValue.content.blocks" class="d-flex flex-column gap-1">
      <div v-for="block in myValue.content.blocks" :key="block.id">
        <div v-if="block.type === 'paragraph'" @click="onTextClick" v-html="block.data.text" />
        <player-video v-else-if="block.type === 'video'" :uuid="block.data.uuid" />
        <player-remote v-else-if="block.type === 'remote-video'" :url="block.data.url" />
        <topic-block-file v-else-if="block.type === 'file'" :block="block" />
        <topic-block-image v-else-if="block.type === 'image'" :block="block" />
        <topic-block-audio v-else-if="block.type === 'audio'" :block="block" />
        <topic-block-code v-else-if="block.type === 'code'" :block="block" />
        <template v-else>{{ block }}</template>
      </div>
    </div>
    <topic-footer :topic="myValue" />
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  topic: {
    type: Object as PropType<VespTopic>,
    default() {
      return {}
    },
  },
})

const myValue: Ref<VespTopic> = ref(props.topic)
const {$socket} = useNuxtApp()

function onTextClick(e: MouseEvent) {
  const target = e.target as HTMLLinkElement
  // External link
  if (target.tagName === 'A' && /:\/\//.test(target.href)) {
    e.preventDefault()
    window.open(target.href)
  }
}

onMounted(() => {
  $socket.on('topics', (data: any) => {
    if (data.uuid === myValue.value.uuid) {
      Object.keys(myValue.value).forEach((key: string) => {
        if (data[key] !== undefined) {
          myValue.value[key] = data[key]
        }
      })
    }
  })
})
</script>
