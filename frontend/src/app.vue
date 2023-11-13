<template>
  <nuxt-layout>
    <nuxt-loading-indicator color="var(--bs-primary)" :throttle="0" />
    <nuxt-page />
  </nuxt-layout>
</template>

<script setup lang="ts">
const {user} = useAuth()
const {$isMobile} = useNuxtApp()

watch(user, () => {
  if (user.value) {
    const error = useError()
    if (!(error.value instanceof Error) && error.value?.statusCode === 401) {
      clearError()
    }
  } else {
    const name = String(useRoute()?.name)
    if (name.startsWith('user') || name.startsWith('admin')) {
      navigateTo('/')
    }
  }
})

function handleResize() {
  const width = process.client ? window.innerWidth : 768
  $isMobile.value = width < 768
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>
