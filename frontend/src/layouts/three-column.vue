<template>
  <div id="three-column" :class="{'d-flex flex-column min-vh-100 bg-light': true, 'main-page': isMainPage}">
    <app-navbar class="bg-white border-bottom" />

    <div class="main-background">
      <b-img :src="background" height="240" />
    </div>

    <b-container class="pt-4 flex-grow-1">
      <b-row class="main-cols">
        <b-col md="3" class="col-start-wrapper">
          <div class="col-start">
            <div class="main-poster">
              <b-img v-if="poster" :src="poster" width="225" height="280" fluid />
            </div>

            <div class="mt-4">
              <h5 class="text-center">{{ $setting('title') }}</h5>
              <p class="small">{{ $setting('description') }}</p>
            </div>
          </div>
        </b-col>
        <b-col md="6" class="col-center-wrapper">
          <div class="col-center">
            <slot name="default" />
          </div>
        </b-col>
        <b-col md="3" class="col-end-wrapper">
          <div class="col-end">Здесь будут уровни подписок</div>
        </b-col>
      </b-row>
    </b-container>

    <app-footer class="bg-white border-top" />
  </div>
</template>

<script setup lang="ts">
const {$setting, $image} = useNuxtApp()
const route = useRoute()

const background = computed(() => {
  const bg = $setting('background')
  return bg ? $image(bg, {h: 480, fit: 'crop-center'}) : ''
})

const poster = computed(() => {
  const poster = $setting('poster')
  return poster ? $image(poster, {w: 450, h: 560, fit: 'crop'}) : ''
})

const isMainPage = computed(() => {
  return route.name === 'index'
})
</script>
