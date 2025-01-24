<template>
  <div>
    <div v-if="!isTopic && topic.category" class="topic-category">
      <div v-if="route.params.topics === topic.category.uri" class="fw-bold">{{ topic.category.title }} /</div>
      <BLink v-else :to="{name: 'topics', params: {topics: topic.category.uri}}">{{ topic.category.title }} /</BLink>
    </div>
    <div v-if="topic.tags?.length" class="topic-tags">
      <BBadge v-for="(tag, idx) in topic.tags" :key="idx" v-bind="getTagParams(tag)" class="px-2 py-1">
        <div>{{ tag.title }}</div>
      </BBadge>
    </div>
    <div class="topic-footer">
      <div class="d-flex flex-wrap gap-3 order-0 me-auto">
        <UserReactions :item="topic">
          <template #default="{selected, total}">
            <VespFa :icon="[selected ? 'fas' : 'far', 'face-smile']" class="fa-fw" /> {{ formatBigNumber(total) }}
          </template>
        </UserReactions>
        <div><VespFa icon="eye" class="fa-fw" /> {{ formatBigNumber(viewsCount) }}</div>
        <div v-if="!isTopic">
          <BLink v-if="topic.access && commentsCount" :to="link">
            <VespFa icon="comment" class="fa-fw" />
            {{ formatBigNumber(commentsCount) }}
            <span v-if="unseenCount" class="text-success">+{{ formatBigNumber(unseenCount) }}</span>
          </BLink>
          <template v-else> <VespFa icon="comment" class="fa-fw" /> {{ formatBigNumber(commentsCount) }} </template>
        </div>
      </div>
      <div class="col-12 col-md-auto order-3 order-md-1">
        <template v-if="topic.user">
          <VespFa icon="user" class="fa-fw" />
          {{ topic.user.fullname }}
        </template>
      </div>
      <div class="ms-md-auto order-2 order-md-3">
        <template v-if="topic.published_at || topic.publish_at">
          <VespFa icon="calendar" class="fa-fw" />
          <span data-allow-mismatch>{{ formatDate(topic.published_at || topic.publish_at) }}</span>
        </template>
      </div>
    </div>
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

const {user} = useAuth()
const {$socket} = useNuxtApp()
const route = useRoute()
const isTopic = computed(() => route.params.uuid === props.topic.uuid)
const link = computed(() => {
  return {
    name: 'topics-uuid',
    params: {topics: props.topic.category?.uri || 'topics', uuid: props.topic.uuid},
    hash: '#comments',
  }
})

const commentsCount = ref(props.topic.comments_count || 0)
const viewsCount = ref(props.topic.views_count || 0)
const unseenCount = ref(props.topic.unseen_comments_count || 0)
const tags = computed(() => {
  const value = route.query.tags as string
  return value ? value.split(',') : []
})

function onTopicComments(topic: VespTopic) {
  if (props.topic.id === topic.id) {
    commentsCount.value = topic.comments_count || 0
    if (unseenCount.value > commentsCount.value) {
      unseenCount.value = commentsCount.value
    }
  }
}

function onTopicViews(topic: VespTopic) {
  if (props.topic.id === topic.id) {
    viewsCount.value = topic.views_count || 0
    if (props.topic.viewed_at && user.value && topic.user_id && topic.user_id === user.value.id) {
      unseenCount.value = topic.unseen_comments_count || 0
    }
  }
}

function onCommentCreate(comment: VespComment) {
  if (props.topic.id === comment.topic_id) {
    if (comment.created_at && props.topic.viewed_at && comment.created_at > props.topic.viewed_at) {
      if (user.value && comment.user_id !== user.value.id) {
        unseenCount.value++
      }
    }
  }
}

function onCommentDelete(comment: VespComment) {
  if (props.topic.id === comment.topic_id) {
    if (comment.created_at && props.topic.viewed_at && comment.created_at > props.topic.viewed_at) {
      if (user.value && comment.user_id !== user.value.id) {
        unseenCount.value--
        if (unseenCount.value < 0) {
          unseenCount.value = 0
        } else if (unseenCount.value > commentsCount.value) {
          unseenCount.value = commentsCount.value
        }
      }
    }
  }
}

function getTagParams(tag: VespTag) {
  const id = String(tag.id)
  const isActive = tags.value.includes(id)
  const values = [...tags.value]

  const params: Record<string, any> = {}
  if (!isActive) {
    values.push(id)
  } else {
    params.variant = 'primary'
    const idx = values.findIndex((i: string) => i === id)
    values.splice(idx, 1)
  }
  params.to = {
    name: route.name,
    params: route.params,
    query: {...route.query, tags: values.length ? values.join(',') : undefined},
  }

  return params
}

onMounted(() => {
  $socket.on('topic-views', onTopicViews)
  if (!isTopic.value) {
    $socket.on('topic-comments', onTopicComments)
    $socket.on('comment-create', onCommentCreate)
    $socket.on('comment-delete', onCommentDelete)
  }
})

onUnmounted(() => {
  $socket.off('topic-views', onTopicViews)
  if (!isTopic.value) {
    $socket.off('topic-comments', onTopicComments)
    $socket.off('comment-create', onCommentCreate)
    $socket.off('comment-delete', onCommentDelete)
  }
})
</script>
