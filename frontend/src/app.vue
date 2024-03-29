<template>
  <div>
    <nuxt-loading-indicator color="var(--bs-primary)" :throttle="0" />

    <div id="layout" :class="mainClasses">
      <app-navbar class="border-bottom" :sidebar="!isAdmin" />

      <div v-if="isColumns" class="main-background">
        <b-img :src="background" height="240" />
      </div>

      <b-container class="pt-4 flex-grow-1">
        <div v-if="isColumns">
          <b-row class="main-columns">
            <b-col md="8" :class="{offset: $isMobile}">
              <slot>
                <nuxt-page />
              </slot>
            </b-col>
            <b-col v-if="!$isMobile" md="4" class="offset">
              <div class="column">
                <widgets-author />
              </div>

              <widgets-online v-if="showOnline" class="column mt-4" />
              <widgets-levels class="column mt-4" />
              <widgets-tags class="column mt-4" />
            </b-col>
          </b-row>
        </div>
        <div v-else>
          <slot>
            <nuxt-page />
          </slot>
        </div>
      </b-container>

      <app-sidebar v-if="$isMobile" :show-online="isColumns && showOnline" />
      <app-footer class="border-top" />
      <app-payment />
    </div>
  </div>
</template>

<script setup lang="ts">
const {$settings, $image, $isMobile} = useNuxtApp()
const router = useRouter()
const route = useRoute()
const isColumns = computed(() => {
  const route = router.currentRoute.value?.name as string
  return route && (route === 'index' || route.startsWith('topics-'))
})
const isAdmin = computed(() => {
  const route = router.currentRoute.value?.name as string
  return route && route.startsWith('admin')
})
const background = computed(() => {
  const bg = $settings.value.background as VespFile
  return bg ? $image(bg, {h: 480, fit: 'crop-center'}) : ''
})
const showOnline = useRuntimeConfig().public.COMMENTS_SHOW_ONLINE === '1'

const mainClasses = computed(() => {
  const arr = ['d-flex', 'flex-column', 'min-vh-100']
  if (isColumns.value) {
    arr.push('columns')
  }
  if (route.name === 'index') {
    arr.push('main-page')
  }
  return arr
})

function handleResize() {
  const width = process.client ? window.innerWidth : 768
  $isMobile.value = width < 768
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

const description = String($settings.value.description).replace(/<\/?[^>]+(>|$)/g, '')
useSeoMeta({
  title: $settings.value.title as string,
  ogTitle: $settings.value.title as string,
  description,
  ogDescription: description,
  ogImage: $settings.value.poster ? $image($settings.value.poster as VespFile) : undefined,
  twitterCard: 'summary_large_image',
})
</script>
