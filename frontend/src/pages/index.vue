<template>
  <div>
    <b-overlay :show="loading" opacity="0.5" class="d-flex flex-column gap-4">
      <topic-intro v-for="topic in topics" :key="topic.id + topic.access" :topic="topic" class="column">
        <template #header="{title, access}">
          <h2>
            <b-link v-if="access" :to="{name: 'topics-uuid', params: {uuid: topic.uuid}}" class="text-decoration-none">
              {{ title }}
            </b-link>
            <template v-else>{{ title }}</template>
          </h2>
        </template>
      </topic-intro>
    </b-overlay>

    <b-pagination
      v-if="total > limit"
      v-model="page"
      :total-rows="total"
      :per-page="limit"
      :limit="5"
      :hide-goto-end-buttons="true"
      align="center"
      class="mt-4"
    />
  </div>
</template>

<script setup lang="ts">
const {t} = useI18n()
const {user} = useAuth()
const {$socket, $settings} = useNuxtApp()
const loading = ref(false)
const topics = ref()
const total = ref(0)
const page = ref(1)
const limit = 12

async function fetch() {
  loading.value = true
  try {
    const params: Record<string, any> = {limit}
    if (page.value > 1) {
      params.page = page.value
    }
    const data = await useGet('web/topics', params)
    topics.value = data.rows
    total.value = data.total
  } catch (e) {
  } finally {
    loading.value = false
  }
}

await fetch()

onMounted(() => {
  $socket.on('test', (data: any) => {
    console.log(data)
  })
})

definePageMeta({
  layout: 'layout-columns',
})

watch([page, user], () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
  fetch()
})

useHead({
  title: () => [t('pages.index'), $settings.value.title].join(' / '),
})
</script>
