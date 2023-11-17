<template>
  <div class="topic teaser">
    <slot name="header" v-bind="myValue">
      <h2>{{ myValue.title }}</h2>
    </slot>
    <div v-if="topic.cover" class="cover">
      <b-img :src="$image(topic.cover, {w: 1024, h: 768, fit: 'crop'})" class="rounded" fluid />
    </div>
    <div v-if="topic.teaser" class="text">{{ topic.teaser }}</div>
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
