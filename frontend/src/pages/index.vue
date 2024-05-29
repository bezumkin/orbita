<template>
  <div>
    <BButton v-if="$scope('topics/put')" :to="{name: 'topics-create'}" variant="primary" class="mb-3">
      <VespFa icon="plus" fixed-width /> {{ $t('actions.create') }}
    </BButton>

    <BOverlay :show="pending" opacity="0.5" class="topics">
      <template v-if="topics.length">
        <TopicIntro v-for="topic in topics" :key="topic.id" :topic="topic" />
      </template>
      <div v-else class="alert alert-info">
        {{ $t(tags.length ? 'components.table.no_results' : 'components.table.no_data') }}
      </div>
    </BOverlay>

    <BPagination
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
const {$settings} = useNuxtApp()
const route = useRoute()
const router = useRouter()
const page = ref(Number(route.query.page) || 1)
const limit = 12
const tags = computed(() => route.query.tags || [])
const {data, refresh, pending} = await useCustomFetch('web/topics', {query: {page, limit, tags}, watch: false})
const topics = computed(() => data.value?.rows || [])
const total = computed(() => data.value?.total || 0)

function onScroll() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}

watch(page, (newValue) => {
  router.push({name: 'index', query: {...route.query, page: newValue > 1 ? String(newValue) : undefined}})
  refresh()
  onScroll()
})

watch(tags, (newValue, oldValue) => {
  if (newValue.length !== oldValue.length) {
    page.value = 1
    refresh()
    onScroll()
  }
})

onMounted(() => {
  setTimeout(onScroll, 100)
  if (!topics.value.length) {
    if (page.value > 1) {
      navigateTo({name: 'index', query: {...route.query, page: undefined}})
    } else if (tags.value.length) {
      navigateTo({name: 'index', query: {...route.query, tags: undefined}})
    }
  }
})

useHead({
  title: () => [t('pages.index'), $settings.value.title].join(' / '),
})
</script>
