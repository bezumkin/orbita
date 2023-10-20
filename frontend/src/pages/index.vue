<template>
  <div class="p-4">
    <div>Здесь будет лента постов</div>
    <div class="mt-4">
      <b-link :to="{name: 'posts-uuid', params: {uuid: '4edc4d74-fdb9-40cd-b61d-6598ba524a00'}}">
        Тестовая ссылка &rarr;
      </b-link>
    </div>
  </div>
</template>

<script setup lang="ts">
const {t} = useI18n()
const {$socket, $setting} = useNuxtApp()

onMounted(() => {
  $socket.on('test', (data: any) => {
    console.log(data)
  })
})

definePageMeta({
  layout: 'three-column',
})

useHead({
  title: () => [t('pages.index'), $setting('title')].join(' / '),
})
</script>
