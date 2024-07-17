<template>
  <div>
    <BButton v-if="$scope('topics/put')" :to="{name: 'topics-create'}" variant="primary" class="mb-3">
      <VespFa icon="plus" fixed-width /> {{ $t('actions.create') }}
    </BButton>

    <BOverlay :show="loading" opacity="0.5" class="topics">
      <template v-if="topics.length">
        <TopicIntro v-for="topic in topics" :key="topic.id" :topic="topic" />
      </template>
      <div v-else class="alert alert-info">
        {{ $t(tags.length ? 'components.table.no_results' : 'components.table.no_data') }}
      </div>
    </BOverlay>

    <div v-show="canFetch" ref="spinner" class="mt-5 text-center">
      <BSpinner />
    </div>
  </div>
</template>

<script setup lang="ts">
const store = useTopicsStore()
const {page, topics, total, loading, query} = storeToRefs(store)
const {fetch, refresh} = store
const {t} = useI18n()
const {$settings, $socket} = useNuxtApp()
const {loggedIn} = useAuth()
const route = useRoute()
const limit = 12
const tags = computed(() => route.query.tags as string)
const canFetch = computed(() => {
  return total.value > page.value * limit
})
const spinner = ref()

function onScroll() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}

function initObserver() {
  const observer = new IntersectionObserver((entries) => {
    const {isIntersecting} = entries[0]
    if (isIntersecting && canFetch.value && !loading.value) {
      page.value++
      fetch(tags.value)
    }
  })
  observer.observe(spinner.value)
}

onMounted(() => {
  initObserver()
  $socket.on('topics-refresh', () => {
    if (page.value === 1) {
      fetch(tags.value)
    }
  })
})

onUnmounted(() => {
  $socket.off('topics-refresh')
})

useHead({
  title: () => [t('pages.index'), $settings.value.title].join(' / '),
})

watch([tags, loggedIn], async () => {
  await refresh(tags.value as string)
  await nextTick(() => {
    setTimeout(onScroll, 100)
  })
})

if (route.query.page) {
  await navigateTo({name: 'index'}, {redirectCode: 301})
}

if (!total.value || query.value.tags !== tags.value) {
  await fetch(tags.value)
}

if (!total.value && tags.value.length) {
  navigateTo({name: 'index'})
}
</script>
