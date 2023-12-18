<template>
  <div v-if="tags.length" class="widget">
    <h5 class="widget-title">{{ $t('widgets.tags') }}</h5>
    <div class="d-flex flex-wrap gap-1">
      <b-badge v-for="(tag, idx) in tags" :key="idx" v-bind="getTagParams(tag)" class="px-2 py-1 d-flex">
        {{ tag.title }}
        <span class="ms-2 ps-2 border-start">{{ tag.topic_tags_count }}</span>
      </b-badge>
    </div>
  </div>
</template>

<script setup lang="ts">
const {$socket} = useNuxtApp()
const route = useRoute()
const {data, refresh} = useCustomFetch('web/tags', {query: {combo: true, limit: 0}})
const tags = computed(() => data.value?.rows || [])
const selected = computed(() => {
  const value = route.query.tags as string
  return value ? value.split(',') : []
})

function getTagParams(tag: VespTag) {
  const id = String(tag.id)
  const isActive = selected.value.includes(id)
  const values = [...selected.value]

  const params: Record<string, any> = {}
  if (!isActive) {
    values.push(id)
  } else {
    params.variant = 'primary'
    const idx = values.findIndex((i: string) => i === id)
    values.splice(idx, 1)
  }
  params.to = {name: 'index', query: values.length ? {tags: values.join(',')} : undefined}

  return params
}

onMounted(() => {
  $socket.on('tags', refresh)
})

onUnmounted(() => {
  $socket.off('tags', refresh)
})
</script>
