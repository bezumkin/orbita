<template>
  <iframe :src="url" v-bind="frameProps" />
</template>

<script setup lang="ts">
defineProps({
  url: {
    type: String,
    required: true,
  },
  autoplay: {
    type: Boolean,
    default: false,
  },
})

const frameProps = computed(() => {
  return {
    width: '1280',
    height: '720',
    allowfullscreen: true,
    frameborder: '0',
    allow: 'clipboard-write; autoplay; encrypted-media; fullscreen; picture-in-picture',
    class: 'rounded',
  }
})
</script>
