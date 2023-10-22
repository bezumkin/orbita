<template>
  <div>
    <div class="main-poster">
      <b-img v-if="poster" :src="poster" width="225" height="280" fluid />
    </div>

    <div class="mt-4">
      <h5 class="text-center">{{ $settings.title }}</h5>
      <p class="small">{{ $settings.description }}</p>
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
