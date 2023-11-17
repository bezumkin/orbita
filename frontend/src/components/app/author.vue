<template>
  <div>
    <b-img v-if="poster" :src="poster" width="225" height="280" class="d-block m-auto rounded" fluid />

    <div class="mt-4 text-center">
      <h5>{{ $settings.title }}</h5>
      <p class="small text-pre" v-html="$settings.description" />
    </div>
  </div>
</template>

<script setup lang="ts">
const {$settings, $image} = useNuxtApp()

const poster = computed(() => {
  const poster = $settings.value.poster
  return poster ? $image(poster, {w: 450, h: 560, fit: 'crop'}) : ''
})
</script>
