<template>
  <div class="p-4">
    <b-link :to="{name: 'index'}">&larr; Назад</b-link>
    <div class="mt-4">Пост с id = {{ uuid }}</div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const uuid = computed(() => {
  return route.params.uuid
})

definePageMeta({
  layout: 'three-column',
})
</script>
