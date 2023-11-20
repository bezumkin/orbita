<template>
  <div>
    <template v-if="topic?.access">
      <topic-content :topic="topic" class="column" :list-view="false">
        <template #header="{title}">
          <b-link :to="{name: 'index'}">&larr; {{ $t('actions.back') }}</b-link>
          <h1>{{ title }}</h1>
        </template>
      </topic-content>
      <comments-tree :topic="topic" class="column mt-4" @comment-view="onCommentView" />
    </template>
    <topic-intro v-else :topic="topic" class="column mt-4" :list-view="false">
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
const {data, error} = await useCustomFetch('web/topics/' + route.params.uuid)
const topic: ComputedRef<VespTopic | undefined> = computed(() => data.value || {})
const topics: Ref<string[] | undefined> = useCookie('topics', {sameSite: true})
if (!topics.value) {
  topics.value = []
}
const timeout = ref()

if (error.value) {
  showError({statusCode: error.value.statusCode || 500, statusMessage: error.value.statusMessage || 'Server Error'})
}

async function saveView(updateView: boolean) {
  if (!topic.value || !topic.value.uuid || !topic.value.access) {
    return
  }
  if (!user.value && topics.value && topics.value.includes(topic.value.uuid)) {
    return
  }
  try {
    const data = await usePost('web/topics/' + topic.value.uuid + '/view')
    if (topic.value) {
      if (data.views_count) {
        topic.value.views_count = data.views_count
      }
      if (updateView && data.viewed_at) {
        topic.value.viewed_at = data.viewed_at
      }
    }
    if (!user.value && topics.value) {
      topics.value.push(topic.value.uuid)
    }
  } catch (e) {}
}

function onCommentView(comment: VespComment) {
  if (!topic.value || !topic.value.viewed_at || !comment.created_at || topic.value.viewed_at >= comment.created_at) {
    return
  }
  if (timeout.value) {
    clearTimeout(timeout.value)
  }
  timeout.value = setTimeout(() => saveView(true), 5000)
}

watch(
  () => topic.value?.access,
  () => saveView(false),
)

onMounted(() => {
  if (error.value) {
    clearError()
  } else {
    timeout.value = setTimeout(() => saveView(false), 2500)
  }
})

onUnmounted(() => {
  if (timeout.value) {
    clearTimeout(timeout.value)
  }
})

useHead({
  title: () => [topic.value?.title, t('pages.topics'), $settings.value.title].join(' / '),
})
</script>
