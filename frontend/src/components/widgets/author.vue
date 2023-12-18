<template>
  <div v-if="$settings.poster || $settings.description">
    <b-img v-if="$settings.poster" v-bind="posterProps" fluid />

    <div class="mt-4 text-center mx-auto" style="max-width: 300px">
      <h5>{{ $settings.title }}</h5>
      <p class="small text-pre" @click="$contentClick" v-html="$settings.description" />
    </div>
  </div>
</template>

<script setup lang="ts">
const {$settings, $image} = useNuxtApp()
const route = useRoute()

const posterProps = computed(() => {
  const data: Record<string, any> = {}
  if (!$settings.value.poster) {
    return data
  }

  data.style = 'transition: all 0.25s'
  data.class = ['d-block', 'm-auto']
  if (route.name === 'index') {
    data.width = 225
    data.height = 280
    data.class.push('rounded')
  } else {
    data.width = 150
    data.height = 150
    data.class.push('rounded-circle')
  }
  data.src = $image($settings.value.poster, {w: data.width, h: data.height, fit: 'crop'})
  data.srcSet = $image($settings.value.poster, {w: data.width * 2, h: data.height * 2, fit: 'crop'}) + ' 2x'

  return data
})
</script>
