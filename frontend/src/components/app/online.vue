<template>
  <div class="widget">
    <h5 class="widget-title">{{ $t('widgets.comments') }}</h5>
    <b-overlay class="widget-body comments" :show="pending" opacity="0.5">
      <div v-for="comment in comments" :key="comment.id" class="comment">
        <div v-if="comment.user" class="comment-header">
          <user-avatar :user="comment.user" class="me-1" />
          <div class="d-flex flex-grow-1 flex-wrap align-items-center justify-content-between">
            <div>{{ comment.user.fullname }}</div>
            <div v-if="comment.created_at" class="small">{{ d(comment.created_at, 'long') }}</div>
          </div>
        </div>
        <div class="comment-text">{{ $contentPreview(comment.content, 100) }}</div>
        <div v-if="comment.topic" class="comment-footer">
          <b-link :to="getCommentLink(comment)" class="me-2">
            <fa icon="file" class="fa-fw" />
            {{ comment.topic.title }}
          </b-link>
          <div class="ms-auto text-nowrap"><fa icon="comments" /> {{ comment.topic.comments_count }}</div>
        </div>
      </div>
    </b-overlay>
  </div>
</template>

<script setup lang="ts">
const url = 'web/comments/latest'
const {d} = useI18n()
const {$socket} = useNuxtApp()
const {data, refresh, pending} = useCustomFetch(url, {query: {limit: 10}})
const comments: ComputedRef<VespComment[]> = computed(() => data.value?.rows || [])

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
