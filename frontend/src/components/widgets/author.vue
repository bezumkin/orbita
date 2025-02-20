<template>
  <div v-if="$settings.poster || $settings.description" class="widget">
    <BImg v-if="$settings.poster" v-bind="posterProps" fluid />

    <div class="mt-4 mx-auto text-center col-lg-9">
      <h5 class="widget-title">{{ $settings.title }}</h5>
      <div class="widget-body">
        <div class="text-pre" @click="$contentClick" v-html="$settings.description" />
      </div>
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
  const tmp = {w: data.width, h: data.height, fit: 'crop'}
  data.src = $image($settings.value.poster, tmp)
  data.srcset = $image($settings.value.poster, {...tmp, dpr: 2}) + ' 2x'

  return data
})
</script>
