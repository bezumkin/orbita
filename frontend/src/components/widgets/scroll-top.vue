<template>
  <div class="widget scroll-top">
    <BButton variant="light" :style="{opacity}" @click="scrollToTop">&uarr; {{ $t('actions.scrollTop') }}</BButton>
  </div>
</template>

<script setup lang="ts">
const opacity = ref('0')

function onScroll() {
  opacity.value = window.scrollY > window.screen.height ? '1' : '0'
}

onMounted(() => {
  document.addEventListener('scroll', onScroll)
})

onUnmounted(() => {
  document.removeEventListener('scroll', onScroll)
})
</script>
