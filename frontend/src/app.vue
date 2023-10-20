<template>
  <nuxt-layout>
    <nuxt-page />
  </nuxt-layout>
</template>

<script setup lang="ts">
const {data: user} = useAuth()

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
</script>
