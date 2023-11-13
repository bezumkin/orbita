<template>
  <div>
    <template v-if="topic?.access">
      <topic-content :topic="topic" class="column">
        <template #header="{title}">
          <b-link :to="{name: 'index'}">&larr; {{ $t('actions.back') }}</b-link>
          <h1>{{ title }}</h1>
        </template>
      </topic-content>
      <topic-comments :topic="topic" class="column mt-4" />
    </template>
    <topic-intro v-else :topic="topic" class="column mt-4">
      <template #header="{title}">
        <b-link :to="{name: 'index'}">&larr; {{ $t('actions.back') }}</b-link>
        <h2>{{ title }}</h2>
      </template>
    </topic-intro>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const {$settings} = useNuxtApp()
const {t} = useI18n()
const {user} = useAuth()
const {data} = useGet('web/topics/' + route.params.uuid)
const topic: ComputedRef<VespTopic | undefined> = computed(() => data.value || {})
const topics: Ref<string[] | undefined> = useCookie('topics', {sameSite: true})
if (!topics.value) {
  topics.value = []
}

async function saveView() {
  if (!topic.value || !topic.value.uuid || !topic.value.access) {
    return
  }
  if (!user.value && topics.value && topics.value.includes(topic.value.uuid)) {
    return
  }
  try {
    const data = await useApi('web/topics/' + topic.value.uuid + '/view', {method: 'POST'})
    if (topic.value && data.views_count) {
      topic.value.views_count = data.views_count
    }
    if (!user.value && topics.value) {
      topics.value.push(topic.value.uuid)
    }
  } catch (e) {}
}

onMounted(() => {
  nextTick(saveView)
})
watch(() => topic.value?.access, saveView)

definePageMeta({
  layout: 'layout-columns',
})

useHead({
  title: () => [topic.value?.title, t('pages.topics'), $settings.value.title].join(' / '),
})
</script>
