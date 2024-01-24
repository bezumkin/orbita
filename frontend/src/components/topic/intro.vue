<template>
  <div class="topic teaser">
    <div class="topic-cover">
      <b-img v-if="topic.cover" v-bind="imageProps" class="background" lazy />
      <div class="wrapper">
        <div class="text">
          <h2>
            <template v-if="isTopic || !myValue.access">{{ myValue.title }}</template>
            <b-link v-else :to="link">{{ myValue.title }}</b-link>
          </h2>
          <div v-if="topic.teaser">{{ topic.teaser }}</div>
        </div>

        <div v-if="!myValue.access" class="action">
          <div v-if="levelRequired" class="mb-2">
            <span class="fw-bold">{{ $t('components.payment.teaser.required') }}</span>
            {{ levelRequired.title }}
          </div>
          <b-button variant="primary" @click="onSubscribe">
            <vesp-fa icon="lock" />
            {{ $t(myValue.price ? 'components.payment.teaser.buy' : 'components.payment.level.subscribe') }}
          </b-button>
        </div>
        <div v-else class="action">
          <b-button variant="light" :to="link">
            <vesp-fa icon="eye" /> {{ $t('components.payment.teaser.view') }}
          </b-button>
        </div>
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

const {$socket, $payment, $levels, $image} = useNuxtApp()
const isTopic = useRoute().params.uuid === props.topic.uuid
const myValue: Ref<VespTopic> = ref(props.topic)
const link = {name: 'topics-uuid', params: {uuid: myValue.value.uuid}}
const levelRequired = computed(() => {
  if (myValue.value.level_id) {
    return $levels.value.find((i: VespLevel) => i.id === myValue.value.level_id)
  }
  return undefined
})
const imageProps = computed(() => {
  if (!myValue.value.cover) {
    return undefined
  }
  return {
    src: $image(myValue.value.cover, {h: 400, fit: 'crop'}),
    srcSet: $image(myValue.value.cover, {h: 800, fit: 'crop'}) + ' 2x',
  }
})

function onSubscribe() {
  if ($payment.value) {
    $payment.value = undefined
    nextTick(() => {
      $payment.value = props.topic
    })
  } else {
    $payment.value = props.topic
  }
}

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
