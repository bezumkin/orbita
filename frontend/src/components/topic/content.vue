<template>
  <div class="topic">
    <div class="topic-header">
      <h1 class="mt-2">{{ myValue.title }}</h1>
    </div>

    <editor-content v-if="myValue.content?.blocks" :content="myValue.content" class="topic-content" />

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

const {$socket} = useNuxtApp()
const myValue: Ref<VespTopic> = ref(props.topic)

function onTopicUpdate(topic: VespTopic) {
  if (topic.uuid === myValue.value.uuid) {
    Object.keys(myValue.value).forEach((key: string) => {
      if (topic[key] !== undefined) {
        myValue.value[key] = topic[key]
      }
    })
  }
}

onMounted(() => {
  $socket.on('topic-update', onTopicUpdate)
})

onUnmounted(() => {
  $socket.off('topic-update', onTopicUpdate)
})
</script>
