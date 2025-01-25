<template>
  <div v-if="$categories.length" class="widget">
    <h5 class="widget-title">{{ $t('widgets.categories') }}</h5>
    <div class="d-flex flex-wrap gap-2">
      <BButton v-for="(cat, idx) in $categories" :key="idx" v-bind="getCategoryParams(cat)">
        {{ cat.title }}
      </BButton>
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const {$categories} = useNuxtApp()

function getCategoryParams(category: VespCategory) {
  return {
    to: {name: 'topics', params: {topics: category.uri}},
    variant: 'link',
    class: 'p-0',
    disabled: route.name === 'topics' && route.params.topics === category.uri,
  }
}
</script>
