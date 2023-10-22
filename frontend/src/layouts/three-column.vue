<template>
  <div id="three-column" :class="{'d-flex flex-column min-vh-100 bg-light': true, 'main-page': isMainPage}">
    <app-navbar class="bg-white border-bottom" />

    <div class="main-background">
      <b-img :src="background" height="240" />
    </div>

    <b-container class="pt-4 flex-grow-1">
      <b-tabs v-if="$isMobile" v-model="tab" class="d-md-none main-tabs" pills justified>
        <b-tab :title="$t('pages.about')">
          <app-author />

          <app-subscriptions class="mt-5" />
        </b-tab>
        <b-tab :title="$t('pages.posts')">
          <slot name="default" />
        </b-tab>
      </b-tabs>
      <b-row v-else class="d-none d-md-flex main-cols">
        <b-col md="3" class="col-start-wrapper">
          <div class="col-start">
            <app-author />
          </div>
        </b-col>
        <b-col md="6" class="col-center-wrapper">
          <div class="col-center">
            <slot name="default" />
          </div>
        </b-col>
        <b-col md="3" class="col-end-wrapper">
          <div class="col-end">
            <app-subscriptions />
          </div>
        </b-col>
      </b-row>
    </b-container>

    <app-footer class="bg-white border-top" />
  </div>
</template>

<script setup lang="ts">
const {$settings, $image, $isMobile} = useNuxtApp()
const route = useRoute()
const tab = ref(route.hash === '#about' ? 0 : 1)

const background = computed(() => {
  const bg = $settings.value.background
  return bg ? $image(bg, {h: 480, fit: 'crop-center'}) : ''
})

const isMainPage = computed(() => {
  return route.name === 'index'
})

watch(tab, (newValue) => {
  document.location.hash = !newValue ? 'about' : ''
})

onMounted(() => {
  if (route.hash && !$isMobile.value) {
    tab.value = 1
  }
})
</script>
