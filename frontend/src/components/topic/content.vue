<template>
  <BOverlay :show="loading" opacity="0.5" class="topic">
    <div v-if="!record">
      <div v-if="topic.category" class="topic-category">
        <BLink :to="{name: 'topics', params: {topics: topic.category.uri}}"> {{ topic.category.title }} / </BLink>
      </div>
      <div class="topic-header">
        <h1 class="mt-2">
          <span @click="$contentClick" v-html="title" />
          <BButton v-if="$scope('topics/patch')" variant="link" class="ms-2 p-0" @click="onEdit">
            <VespFa icon="edit" class="fa-fw" />
          </BButton>
        </h1>
      </div>
      <EditorContent v-if="blocks" :content="{blocks}" />
      <TopicFooter :topic="topic" />
    </div>
    <BForm v-else-if="$scope('topics/patch')" class="topic-form" @submit.prevent="onSubmit" @keydown="onKeydown">
      <FormsTopic v-model="record" />
      <div class="topic-buttons mb-0 mt-2">
        <BButton :disabled="loading" @click.prevent="onCancel">{{ $t('actions.cancel') }}</BButton>
        <BButton variant="primary" type="submit" :disabled="loading">
          <BSpinner v-if="loading" small />
          {{ $t('actions.save') }}
        </BButton>
      </div>
    </BForm>
  </BOverlay>
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
const topic: Ref<VespTopic> = ref(props.topic)
const loading = ref(false)
const record: Ref<undefined | VespTopic> = ref()

const title = computed(() => {
  const header = topic.value.content?.blocks?.find((i: any) => i.type === 'header' && i.data.level === 1)
  return header?.data.text || props.topic.title
})
const blocks = computed(() => {
  const blocks = topic.value.content?.blocks || []
  const headerIdx = blocks.findIndex((i: any) => i.type === 'header' && i.data.level === 1)
  return headerIdx > -1 ? blocks.toSpliced(headerIdx, 1) : blocks
})

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
    scrollToTop()
    const data = await usePatch('admin/topics/' + props.topic.id, {...record.value})
    onTopicUpdate(data)
    onCancel()
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function onCancel() {
  record.value = undefined
}

function onTopicUpdate(newValue: VespTopic) {
  if (newValue.uuid === topic.value.uuid) {
    if (newValue.category?.uri !== topic.value?.category?.uri) {
      useRouter().replace({
        name: 'topics-uuid',
        params: {topics: newValue.category?.uri || 'topics'},
      })
    } else {
      Object.keys(topic.value).forEach((key: string) => {
        if (newValue[key] !== undefined) {
          topic.value[key] = newValue[key]
        }
      })
    }
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
