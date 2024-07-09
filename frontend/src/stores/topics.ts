import {defineStore} from 'pinia'

export const useTopicsStore = defineStore('topics', () => {
  const total = ref(0)
  const topics: Ref<VespTopic[]> = ref([])
  const loading = ref(false)
  const page = ref(1)

  async function fetch(tags: string = '', limit: number = 12) {
    loading.value = true
    try {
      const data = await useGet('web/topics', {page: page.value, limit, tags})
      total.value = data.total
      if (page.value === 1) {
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

  async function refresh(tags: string = '', limit: number = 12) {
    page.value = 1
    await fetch(tags, limit)
  }

  return {page, topics, total, loading, fetch, refresh}
})
