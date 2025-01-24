<template>
  <div>
    <template v-if="category">
      <BButton variant="link" class="d-block mb-2 ps-0" @click="handleBackBtn()">
        &larr; {{ $t('actions.back') }}
      </BButton>
      <h1 v-if="category" class="mb-4">{{ category.title }}</h1>
    </template>
    <BRow class="align-items-center mb-3">
      <BCol>
        <BButton
          v-if="$scope('topics/put')"
          :to="{name: 'topics-create', params: {topics: category?.uri || 'topics'}}"
          variant="primary"
        >
          <VespFa icon="plus" fixed-width /> {{ $t('actions.create') }}
        </BButton>
      </BCol>
      <BCol cols="auto">
        <BButton v-if="category || query.tags || query.reverse" @click="onReverse">
          <VespFa :icon="query.reverse ? 'arrow-down-short-wide' : 'arrow-up-wide-short'" />
          {{ $t('models.topic.published_at') }}
        </BButton>
      </BCol>
    </BRow>

    <BOverlay :show="loading" opacity="0.5" class="topics">
      <template v-if="topics.length">
        <TopicIntro v-for="topic in topics" :key="topic.id" :topic="topic" />
      </template>
      <div v-else class="alert alert-info">
        {{ $t(query.tags ? 'components.table.no_results' : 'components.table.no_data') }}
      </div>
    </BOverlay>

    <div v-show="canFetch" class="text-center mt-5">
      <BButton v-if="!scroll" @click="onScroll">{{ $t('actions.load_more') }}</BButton>
      <div v-show="scroll" ref="spinner" class="mt-5 text-center">
        <BSpinner />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const router = useRouter()
const route = useRoute()
const store = useTopicsStore()
const {topics, total, loading, query, category} = storeToRefs(store)
const {fetch, refresh} = store
const {t} = useI18n()
const {$settings, $categories} = useNuxtApp()
const {loggedIn} = useAuth()
const canFetch = computed(() => {
  return total.value > query.value.page * query.value.limit
})
const spinner = ref()
const scroll = ref(false)

query.value.reverse = Boolean(route.query.reverse)
query.value.tags = route.query.tags as string

function initObserver() {
  const observer = new IntersectionObserver((entries) => {
    const {isIntersecting} = entries[0]
    if (isIntersecting && canFetch.value && !loading.value) {
      query.value.page++
      fetch()
    }
  })
  observer.observe(spinner.value)
}

function onReverse() {
  query.value.reverse = !query.value.reverse
  router.push({
    name: route.name,
    params: route.params,
    query: {...route.query, reverse: query.value.reverse ? '1' : undefined},
  })
}

async function onScroll() {
  scroll.value = true
  query.value.page++
  await fetch()
  initObserver()
}

function loadCategory() {
  if (route.params.topics) {
    category.value = $categories?.value.find((i: VespCategory) => i.uri === route.params.topics)
    if (!category.value) {
      showError({statusCode: 404, statusMessage: 'Not Found'})
    }
  }
}

watch([() => route.query, loggedIn], async () => {
  query.value.tags = route.query.tags as string
  query.value.reverse = Boolean(route.query.reverse)
  // scroll.value = false
  await refresh()
  await nextTick(() => {
    setTimeout(() => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth',
      })
    }, 100)
  })
})

if (route.query.page) {
  await navigateTo({name: 'index'}, {redirectCode: 301})
}

// Update topics depending on current category
if (route.params.topics) {
  if (category.value?.uri !== route.params.topics) {
    loadCategory()
    if (total.value) {
      await fetch()
    }
  }
} else if (category.value) {
  category.value = undefined
  if (total.value) {
    await fetch()
  }
}

if (!total.value || query.value.tags !== route.query.tags) {
  await fetch()
}

if (!total.value && route.query.tags) {
  navigateTo({name: 'index'})
}

useHead({
  title: () => [category.value?.title || t('pages.index'), $settings.value.title].join(' / '),
})

if (category.value) {
  useSeoMeta({
    title: category.value.title,
    ogTitle: category.value.title,
    description: category.value.description || '',
    ogDescription: category.value.description || '',
  })
}
</script>
