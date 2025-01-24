<template>
  <div>
    <BButton variant="link" class="d-block mb-2 ps-0" @click="handleBackBtn(topic)">
      &larr; {{ $t('actions.back') }}
    </BButton>
    <template v-if="topic && topic.access">
      <TopicContent :topic="topic" class="column" />
      <CommentsTree :topic="topic" class="column mt-4" @comment-view="onCommentView" />
    </template>
    <TopicIntro v-else-if="topic" :topic="topic" />
  </div>
</template>

<script setup lang="ts">
const router = useRouter()
const route = useRoute()
const {$settings, $image} = useNuxtApp()
const {t} = useI18n()
const {user} = useAuth()
const {data, error} = await useCustomFetch('web/topics/' + route.params.uuid)
const topic = computed<VespTopic | undefined>(() => data.value || undefined)
const topics = useCookie<string[] | undefined>('topics')
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
      if (data.viewed_at && (updateView || !topic.value.viewed_at)) {
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

  if (!document.location.hash) {
    window.scrollTo({
      top: 0,
      behavior: 'instant',
    })
  }
})

onUnmounted(() => {
  if (timeout.value) {
    clearTimeout(timeout.value)
  }
})

useHead({
  title: () =>
    [topic.value?.title, topic.value?.category?.title || t('pages.topics'), $settings.value.title].join(' / '),
})

if (topic.value) {
  useSeoMeta({
    title: topic.value.title,
    ogTitle: topic.value.title,
    description: topic.value.teaser,
    ogDescription: topic.value.teaser,
    ogImage: topic.value.cover ? $image(topic.value.cover) : undefined,
    twitterCard: 'summary_large_image',
  })
}

// Check for redirect
if (topic.value?.category) {
  if (route.params.topics !== topic.value.category.uri) {
    sendRedirect()
  }
} else if (route.params.topics !== 'topics') {
  sendRedirect()
}

function sendRedirect() {
  navigateTo(
    {name: 'topics-uuid', params: {topics: topic.value?.category?.uri || 'topics', uuid: topic.value?.uuid}},
    {redirectCode: 302, replace: true},
  )
}
</script>
