<template>
  <div>
    <BRow class="align-items-center mb-3">
      <BCol>
        <BButton v-if="$scope('topics/put')" :to="{name: 'topics-create'}" variant="primary">
          <VespFa icon="plus" fixed-width /> {{ $t('actions.create') }}
        </BButton>
      </BCol>
      <BCol cols="auto">
        <BButton v-if="query.tags || query.reverse" @click="onReverse">
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
const {topics, total, loading, query} = storeToRefs(store)
const {fetch, refresh} = store
const {t} = useI18n()
const {$settings} = useNuxtApp()
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
  router.push({name: 'index', query: {...route.query, reverse: query.value.reverse ? '1' : undefined}})
}

async function onScroll() {
  scroll.value = true
  query.value.page++
  await fetch()
  initObserver()
}

useHead({
  title: () => [t('pages.index'), $settings.value.title].join(' / '),
})

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

if (!total.value || query.value.tags !== route.query.targs) {
  await fetch()
}

if (!total.value && route.query.targs) {
  navigateTo({name: 'index'})
}
</script>
