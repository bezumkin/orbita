<template>
  <div>
    <b-overlay :show="pending" opacity="0.5" class="d-flex flex-column gap-4">
      <topic-intro v-for="topic in topics" :key="topic.id" :topic="topic" :list-view="true" class="column">
        <template #header="{title, access}">
          <h2>
            <b-link v-if="access" :to="{name: 'topics-uuid', params: {uuid: topic.uuid}}">
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
const {$settings} = useNuxtApp()
const page = ref(1)
const limit = 12
const {data, pending} = await useCustomFetch('web/topics', {query: {page, limit}})
const topics = computed(() => data.value?.rows || [])
const total = computed(() => data.value?.total || 0)

watch(page, () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
})

useHead({
  title: () => [t('pages.index'), $settings.value.title].join(' / '),
})
</script>
