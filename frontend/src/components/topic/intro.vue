<template>
  <div :class="{'topic teaser': true, inactive: !topic.active}">
    <div class="topic-cover">
      <BImg v-if="imageProps" v-bind="imageProps" class="background" lazy />
      <div class="wrapper">
        <div class="text">
          <h2>
            <template v-if="isTopic || !myValue.access">{{ myValue.title }}</template>
            <BLink v-else :to="link">{{ myValue.title }}</BLink>
          </h2>
          <div v-if="topic.teaser">{{ topic.teaser }}</div>
        </div>

        <div v-if="!myValue.access" class="action">
          <div v-if="levelRequired" class="mb-2">
            <span class="fw-bold">{{ $t('components.payment.teaser.required') }}</span>
            {{ levelRequired.title }}
          </div>
          <BButton variant="primary" @click="onSubscribe">
            <VespFa icon="lock" />
            {{ $t(myValue.price ? 'components.payment.teaser.buy' : 'components.payment.level.subscribe') }}
          </BButton>
        </div>
        <div v-else class="action">
          <BButton variant="light" :to="link">
            <VespFa icon="eye" /> {{ $t('components.payment.teaser.view') }}
          </BButton>
        </div>
      </div>
    </div>

    <TopicFooter :topic="myValue" />
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

const {$socket, $payment, $levels, $image, $settings} = useNuxtApp()
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
  const file = myValue.value.cover || $settings.value.cover
  if (!file) {
    return undefined
  }
  return {
    src: $image(file as VespFile, {h: 400, fit: 'crop'}),
    srcset: $image(file as VespFile, {h: 800, fit: 'crop'}) + ' 2x',
  }
})

function onSubscribe() {
  let value: VespTopic | VespSubscription = {}
  if (props.topic.price > 0) {
    value = props.topic
  } else if (levelRequired.value) {
    value = levelRequired.value
  } else {
    return
  }

  if ($payment.value) {
    $payment.value = undefined
    nextTick(() => {
      $payment.value = value
    })
  } else {
    $payment.value = value
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
