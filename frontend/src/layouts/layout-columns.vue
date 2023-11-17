<template>
  <div id="layout-columns" :class="{'d-flex flex-column min-vh-100 bg-light': true, 'main-page': isMainPage}">
    <app-navbar class="bg-white border-bottom" />

    <div class="main-background">
      <b-img :src="background" height="240" />
    </div>

    <b-container class="pt-4 flex-grow-1">
      <b-row class="main-columns">
        <b-col md="8" :class="{offset: $isMobile}">
          <slot name="default" />
        </b-col>
        <b-col v-if="!$isMobile" md="4" class="offset">
          <div class="column">
            <app-author />
          </div>
          <div v-if="showOnline" class="column mt-4">
            <app-online />
          </div>
          <div class="column mt-4">
            <app-levels />
          </div>
        </b-col>
      </b-row>
    </b-container>

    <b-offcanvas v-if="$isMobile" id="sidebar" v-model="$sidebar" placement="end" no-header>
      <div class="d-flex flex-column align-items-center">
        <app-author />
        <app-online v-if="showOnline" class="mt-4" />
        <app-levels class="mt-4" />
      </div>
    </b-offcanvas>

    <app-footer class="bg-white border-top" />
  </div>
</template>

<script setup lang="ts">
const {$settings, $image, $isMobile, $sidebar} = useNuxtApp()
const route = useRoute()
const showOnline = useRuntimeConfig().public.COMMENTS_SHOW_ONLINE === '1'

const background = computed(() => {
  const bg = $settings.value.background
  return bg ? $image(bg, {h: 480, fit: 'crop-center'}) : ''
})

const isMainPage = computed(() => {
  return route.name === 'index'
})
</script>
