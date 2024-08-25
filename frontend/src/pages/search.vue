<template>
  <div>
    <BForm class="bg-body-secondary mt-3 p-3 rounded" @submit.prevent="onSearch">
      <BFormGroup>
        <BInputGroup>
          <BFormInput ref="input" v-model="query" autofocus :placeholder="t('pages.search.placeholder')" />
          <template #append>
            <BButton v-if="query !== ''" @click="onReset"><VespFa icon="times" /></BButton>
          </template>
        </BInputGroup>
      </BFormGroup>

      <div class="text-center text-md-start">
        <BButton variant="primary" type="submit" :disabled="!query || loading">
          <VespFa icon="magnifying-glass" /> {{ t('actions.search') }}
        </BButton>
      </div>
    </BForm>

    <BOverlay v-if="total !== null" :show="loading" opacity="0.5" class="mt-5">
      <div v-if="total === 0" class="alert alert-warning" v-text="t('pages.search.no_results')" />
      <template v-else-if="total > 0">
        <BRow align-v="center">
          <BCol md="auto">
            <strong>{{ t('pages.search.results', {total}, total) }}</strong>
          </BCol>
          <BCol md="auto" class="d-flex align-items-center ms-md-auto">
            {{ t('pages.search.sort') }}
            <BButton variant="link" :disabled="sort === ''" @click="onSort('')">
              {{ t('pages.search.sort_default') }}
            </BButton>
            |
            <BButton variant="link" :disabled="sort === 'date'" @click="onSort('date')">
              {{ t('pages.search.sort_date') }}
            </BButton>
          </BCol>
        </BRow>

        <BRow class="mt-4 row-gap-4">
          <BCol v-for="topic in topics" :key="topic.id" md="6">
            <TopicIntro :topic="topic" />
          </BCol>
        </BRow>
      </template>
    </BOverlay>
  </div>
</template>

<script setup lang="ts">
import type {RouteRecordName} from 'vue-router'

const router = useRouter()
const route = useRoute()
const {$settings} = useNuxtApp()
const {t} = useI18n()

const input = ref()
const loading = ref(false)
const query = ref(String(route.query.query || ''))
const sort = ref(route.query.sort === 'date' ? 'date' : '')
const topics = ref<VespTopic[]>([])
const total = ref<null | number>(null)

async function onSearch() {
  if (!query.value) {
    return
  }
  const params: Record<string, string> = {
    query: query.value,
  }
  if (sort.value) {
    params.sort = sort.value
  }

  loading.value = true
  try {
    await router.replace({name: route.name as RouteRecordName, query: params})

    const data = await useGet('web/search', params)
    topics.value = data.rows
    total.value = data.total
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function onSort(type: string) {
  if (sort.value !== type) {
    sort.value = type
    onSearch()
  }
}

function onReset() {
  query.value = ''
  total.value = null
  input.value.focus()
  if (route.query.query) {
    router.replace({name: route.name as RouteRecordName})
  }
}

onMounted(() => {
  if (query.value) {
    onSearch()
  }
})

useHead({
  title: () => [t('pages.search.title'), $settings.value.title].join(' / '),
})
</script>
