import {defineStore} from 'pinia'

export const useTopicsStore = defineStore('topics', () => {
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
    query.value.page = 1
    await fetch()
  }

  return {query, topics, total, loading, fetch, refresh}
})
