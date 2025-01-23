<template>
  <div v-if="comments.length" class="widget">
    <h5 class="widget-title">{{ $t('widgets.comments') }}</h5>
    <BOverlay class="widget-body comments" :show="status === 'pending'" opacity="0.5">
      <div v-for="comment in comments" :key="comment.id" class="comment">
        <div v-if="comment.user" class="comment-header">
          <UserAvatar :user="comment.user" class="me-1" />
          <div class="d-flex flex-grow-1 flex-wrap align-items-center justify-content-between">
            <div>{{ comment.user.fullname }}</div>
            <div v-if="comment.created_at" class="small">{{ formatDate(comment.created_at) }}</div>
          </div>
        </div>
        <div class="comment-text">{{ $contentPreview(comment?.content, 100) }}</div>
        <div v-if="comment.topic" class="comment-footer">
          <BLink v-if="showLink(comment)" :to="getCommentLink(comment)" class="me-2">
            <VespFa icon="file" class="fa-fw" /> {{ comment.topic.title }}
          </BLink>
          <div v-else><VespFa icon="file" class="fa-fw" /> {{ comment.topic.title }}</div>
          <div class="ms-auto text-nowrap"><VespFa icon="comments" /> {{ comment.topic.comments_count }}</div>
        </div>
      </div>
    </BOverlay>
  </div>
</template>

<script setup lang="ts">
const url = 'web/comments/latest'
const {$socket} = useNuxtApp()
const {data, refresh, status} = useCustomFetch(url, {query: {limit: 10}})
const comments: ComputedRef<VespComment[]> = computed(() => data.value?.rows || [])
const route = useRoute()

function showLink(comment: VespComment) {
  return route.name !== 'topics-uuid' || route.params.uuid !== comment.topic?.uuid
}

function getCommentLink(comment: VespComment) {
  return {name: 'topics-uuid', params: {uuid: comment.topic?.uuid}, hash: '#comment-' + comment.id}
}

onMounted(() => {
  $socket.on('comments-latest', refresh)
})

onUnmounted(() => {
  $socket.off('comments-latest', refresh)
})
</script>
