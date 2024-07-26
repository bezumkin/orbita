import {defineStore} from 'pinia'

export const useTopicsStore = defineStore('topics', () => {
  const {$socket, $scope} = useNuxtApp()
  const total = ref(0)
  const topics: Ref<VespTopic[]> = ref([])
  const loading = ref(false)
  const query: Record<string, any> = ref({tags: '', reverse: false, page: 1, limit: 12})

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
  }

  return {query, topics, total, loading, fetch, refresh}
})
