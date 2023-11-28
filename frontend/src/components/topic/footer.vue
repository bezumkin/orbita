<template>
  <div class="topic-footer">
    <div class="d-flex gap-3">
      <div><fa icon="eye" class="fa-fw" /> {{ topic.views_count }}</div>
      <div v-if="!isTopic">
        <b-link
          v-if="topic.access && commentsCount"
          :to="{name: 'topics-uuid', params: {uuid: topic.uuid}, hash: '#comments'}"
        >
          <fa icon="comment" class="fa-fw" />
          {{ commentsCount }}
          <span v-if="unseenCount" class="text-success">+{{ unseenCount }}</span>
        </b-link>
        <template v-else> <fa icon="comment" class="fa-fw" /> {{ commentsCount }} </template>
      </div>
    </div>

    <div v-if="topic.published_at" class="ms-auto">
      <fa icon="calendar" class="fa-fw" /> {{ d(topic.published_at, 'long') }}
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
const {params} = useRoute()
const isTopic = computed(() => params.uuid === props.topic.uuid)

const commentsCount = ref(props.topic.comments_count || 0)
const unseenCount = ref(props.topic.unseen_comments_count || 0)

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
