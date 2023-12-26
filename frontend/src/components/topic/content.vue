<template>
  <b-overlay :show="loading" opacity="0.5" class="topic">
    <div v-if="!record">
      <div class="topic-header">
        <h1 class="mt-2">
          {{ myValue.title }}
          <b-button v-if="$scope('topics/get')" variant="link" class="ms-2 p-0" @click="onEdit">
            <fa icon="edit" class="fa-fw" />
          </b-button>
        </h1>
      </div>
      <editor-content v-if="myValue.content?.blocks" :content="myValue.content" />
      <topic-footer :topic="myValue" />
    </div>
    <b-form v-else-if="$scope('topics/get')" class="topic-form" @submit.prevent="onSubmit" @keydown="onKeydown">
      <div class="topic-buttons">
        <b-button :disabled="loading" @click.prevent="onCancel">{{ $t('actions.cancel') }}</b-button>
        <b-button variant="primary" type="submit" :disabled="loading">
          <b-spinner v-if="loading" small />
          {{ $t('actions.save') }}
        </b-button>
      </div>
      <forms-topic v-model="record" />
    </b-form>
  </b-overlay>
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

const loading = ref(false)
const record: Ref<undefined | VespTopic> = ref()

async function onEdit() {
  try {
    loading.value = true
    record.value = await useGet('admin/topics/' + props.topic.id)
  } catch (e) {
  } finally {
    loading.value = false
  }
}

async function onSubmit() {
  try {
    loading.value = true
    await usePatch('admin/topics/' + props.topic.id, {...record.value})
    onCancel()
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function onCancel() {
  record.value = undefined
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

function onKeydown(e: KeyboardEvent) {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault()
    onSubmit()
  }
}

onMounted(() => {
  $socket.on('topic-update', onTopicUpdate)
})

onUnmounted(() => {
  $socket.off('topic-update', onTopicUpdate)
})
</script>
