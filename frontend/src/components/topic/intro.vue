<template>
  <div class="topic teaser">
    <slot name="header" v-bind="myValue">
      <h2>{{ myValue.title }}</h2>
    </slot>
    <div v-if="topic.cover" class="cover">
      <b-img :src="$image(topic.cover, {w: 1024, h: 768, fit: 'crop'})" class="rounded" fluid />
    </div>
    <div v-if="topic.teaser" class="text">{{ topic.teaser }}</div>
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
