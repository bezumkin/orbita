<template>
  <MainPage>
    <div class="col-md-9 m-auto py-5">
      <h1>{{ error.statusCode }} {{ error.statusMessage }}</h1>
      <h2 v-if="error.message && error.message !== error.statusMessage" class="mt-4">{{ error.message }}</h2>
      <div class="my-4">
        <BLink :to="{name: 'index'}">&larr; {{ $t('errors.index') }}</BLink>
      </div>
      <div v-if="isDev && error.stack" class="mt-4 alert alert-info" style="white-space: pre-line">
        {{ error.stack }}
      </div>
    </div>
  </MainPage>
</template>

<script setup lang="ts">
import MainPage from './app.vue'
const isDev = process.dev
const {fullPath} = useRoute()

const props = defineProps({
  error: {
    type: Object as PropType<{statusCode: number; statusMessage: string; stack?: string; message?: string}>,
    required: true,
  },
})

if (props.error.statusCode === 404 && process.server) {
  try {
    const data = await useGet('web/locate' + fullPath)
    if (data.location) {
      navigateTo(data.location, {redirectCode: data.code || 302})
    }
  } catch (e) {}
}

useHead({
  title: props.error.statusMessage,
})
</script>
