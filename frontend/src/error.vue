<template>
  <MainPage>
    <div class="col-md-9 m-auto py-5">
      <h1>{{ error.statusCode }} {{ error.statusMessage }}</h1>
      <h2 v-if="error.message && error.message !== error.statusMessage" class="mt-4">{{ error.message }}</h2>
      <div v-if="isDev && error.stack" class="mt-4 alert alert-info" style="white-space: pre-line">
        {{ error.stack }}
      </div>
    </div>
  </MainPage>
</template>

<script setup lang="ts">
import MainPage from './app.vue'
const isDev = process.dev

const props = defineProps({
  error: {
    type: Object as PropType<{statusCode: number; statusMessage: string; stack?: string; message?: string}>,
    required: true,
  },
})

useHead({
  title: props.error.statusMessage,
})
</script>
