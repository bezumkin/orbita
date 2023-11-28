<template>
  <div class="col-md-9 m-auto topic">
    <h1 class="topic-header">{{ page.title }}</h1>

    <editor-content :content="page.content" class="topic-content" />
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const {$settings, $socket} = useNuxtApp()
const {data, error} = await useCustomFetch('web/pages/' + route.params.alias)
if (error.value) {
  showError({statusCode: error.value.statusCode || 500, statusMessage: error.value.statusMessage || 'Server Error'})
}
const page: ComputedRef<VespPage> = computed(() => data.value || {})

function onUpdatePage(data: VespPage) {
  if (data.id !== page.value.id) {
    return
  }
  page.value.title = data.title
  page.value.content = data.content
}

onMounted(() => {
  $socket.on('page-update', onUpdatePage)
  if (error.value) {
    clearError()
  }
})

onUnmounted(() => {
  $socket.off('page-update', onUpdatePage)
})

useHead({
  title: () => [page.value?.title, $settings.value.title].join(' / '),
})
</script>
