<template>
  <div>
    <div v-if="topic.tags?.length" class="topic-tags">
      <BBadge v-for="(tag, idx) in topic.tags" :key="idx" v-bind="getTagParams(tag)" class="px-2 py-1">
        <div>{{ tag.title }}</div>
      </BBadge>
    </div>
    <div class="topic-footer">
      <div class="d-flex flex-wrap gap-3 order-0 me-auto">
        <UserReactions :item="topic">
          <template #default="{selected, total}">
            <VespFa :icon="[selected ? 'fas' : 'far', 'face-smile']" class="fa-fw" /> {{ total }}
          </template>
        </UserReactions>
        <div><VespFa icon="eye" class="fa-fw" /> {{ topic.views_count }}</div>
        <div v-if="!isTopic">
          <BLink
            v-if="topic.access && commentsCount"
            :to="{name: 'topics-uuid', params: {uuid: topic.uuid}, hash: '#comments'}"
          >
            <VespFa icon="comment" class="fa-fw" />
            {{ commentsCount }}
            <span v-if="unseenCount" class="text-success">+{{ unseenCount }}</span>
          </BLink>
          <template v-else> <VespFa icon="comment" class="fa-fw" /> {{ commentsCount }} </template>
        </div>
      </div>
      <div class="col-12 col-md-auto order-3 order-md-1">
        <template v-if="topic.user">
          <VespFa icon="user" class="fa-fw" />
          {{ topic.user.fullname }}
        </template>
      </div>
      <div class="ms-md-auto order-2 order-md-3">
        <template v-if="topic.published_at">
          <VespFa icon="calendar" class="fa-fw" />
          {{ d(topic.published_at, 'long') }}
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

const {d} = useI18n()
const {$socket} = useNuxtApp()
const route = useRoute()
const isTopic = computed(() => route.params.uuid === props.topic.uuid)

const commentsCount = ref(props.topic.comments_count || 0)
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

function onCommentCreate(comment: VespComment) {
  if (props.topic.id === comment.topic_id) {
    if (comment.created_at && props.topic.viewed_at && comment.created_at > props.topic.viewed_at) {
      unseenCount.value++
    }
  }
}

function onCommentDelete(comment: VespComment) {
  if (props.topic.id === comment.topic_id) {
    if (comment.created_at && props.topic.viewed_at && comment.created_at > props.topic.viewed_at) {
      unseenCount.value--
      if (unseenCount.value < 0) {
        unseenCount.value = 0
      } else if (unseenCount.value > commentsCount.value) {
        unseenCount.value = commentsCount.value
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
  params.to = {name: 'index', query: {...route.query, tags: values.length ? values.join(',') : undefined}}

  return params
}

if (!isTopic.value) {
  onMounted(() => {
    $socket.on('topic-comments', onTopicComments)
    $socket.on('comment-create', onCommentCreate)
    $socket.on('comment-delete', onCommentDelete)
  })

  onUnmounted(() => {
    $socket.off('topic-comments', onTopicComments)
    $socket.off('comment-create', onCommentCreate)
    $socket.off('comment-delete', onCommentDelete)
  })
}
</script>
