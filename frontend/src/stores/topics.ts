import {defineStore} from 'pinia'

export const useTopicsStore = defineStore('topics', () => {
  const {$socket, $scope} = useNuxtApp()
  const total = ref(0)
  const topics: Ref<VespTopic[]> = ref([])
  const loading = ref(false)
  const query: Record<string, any> = ref({tags: '', reverse: false, page: 1, limit: 12})
  const {user} = useAuth()

  async function fetch() {
    loading.value = true
    try {
      const data = await useGet('web/topics', {
        tags: query.value.tags,
        reverse: Number(query.value.reverse),
        page: query.value.page,
        limit: query.value.limit,
      })
      total.value = data.total
      if (query.value.page === 1) {
        topics.value = data.rows
      } else {
        data.rows.forEach((i: VespTopic) => {
          topics.value.push(i)
        })
      }
    } catch (e) {
    } finally {
      loading.value = false
    }
  }

  async function refresh() {
    topics.value = []
    query.value.page = 1
    await fetch()
  }

  if ($socket) {
    function remove({uuid}: VespTopic) {
      topics.value = topics.value.filter((topic: VespTopic) => topic.uuid !== uuid)
    }

    function update(topic: VespTopic) {
      const idx = topics.value.findIndex(({uuid}: VespTopic) => uuid === topic.uuid)
      if (idx > -1) {
        Object.keys(topic).forEach((k) => {
          if (topics.value[idx][k] !== undefined) {
            topics.value[idx][k] = topic[k]
          }
        })
      } else {
        refresh()
      }
    }

    $socket.on('topic-create', () => {
      if ($scope('topics/patch')) {
        refresh()
      }
    })
    $socket.on('topic-publish', () => {
      if (!$scope('topics/patch')) {
        refresh()
      }
    })
    $socket.on('topic-update', (topic: VespTopic) => {
      if (!topic.active && !$scope('topics/patch')) {
        remove(topic)
      } else {
        update(topic)
      }
    })
    $socket.on('topic-delete', remove)

    $socket.on('topic-comments', (topic: VespTopic) => {
      const found = topics.value.find((i: VespTopic) => i.uuid === topic.uuid)
      if (found) {
        found.comments_count = topic.comments_count || 0
      }
    })

    $socket.on('topic-views', (view: Record<string, any>) => {
      const found = topics.value.find((i: VespTopic) => i.uuid === view.uuid)
      if (found) {
        found.views_count = view.views_count || 0
        if (view.user_id && view.user_id === user.value.id) {
          found.viewed_at = view.viewed_at
          found.unseen_comments_count = view.unseen_comments_count || 0
        }
      }
    })

    $socket.on('comment-create', (comment: VespComment) => {
      const topic = topics.value.find((i: VespTopic) => i.uuid === comment.topic.uuid)
      if (topic) {
        topic.comments_count++
        if (comment.created_at && topic.viewed_at && comment.created_at > topic.viewed_at) {
          if (user.value && comment.user_id !== user.value.id) {
            topic.unseen_comments_count++
          }
        }
      }
    })
    $socket.on('comment-delete', (comment: VespComment) => {
      const topic = topics.value.find((i: VespTopic) => i.uuid === comment.topic.uuid)
      if (topic) {
        topic.comments_count--
        if (comment.created_at && topic.viewed_at && comment.created_at > topic.viewed_at) {
          if (user.value && comment.user_id !== user.value.id) {
            topic.unseen_comments_count--
            if (topic.unseen_comments_count < 0) {
              topic.unseen_comments_count = 0
            } else if (topic.unseen_comments_count > topic.comments_count) {
              topic.unseen_comments_count = topic.comments_count
            }
          }
        }
      }
    })
  }

  return {query, topics, total, loading, fetch, refresh}
})
