<template>
  <div class="topic content">
    <slot name="header" v-bind="myValue">
      <h1>{{ myValue.title }}</h1>
    </slot>
    <div v-if="myValue.content?.blocks" class="d-flex flex-column gap-1">
      <editor-content :content="myValue.content" type="topic" />
    </div>
    <topic-footer :topic="myValue" :list-view="listView" />
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
  listView: {
    type: Boolean,
    default: false,
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
