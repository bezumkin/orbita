<template>
  <div class="d-flex flex-column min-vh-100">
    <app-navbar />

    <b-container class="pt-4 flex-grow-1">
      <slot name="default">
        <nuxt-page />
      </slot>
    </b-container>

    <app-footer />
  </div>
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
