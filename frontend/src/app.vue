<template>
  <div>
    <nuxt-loading-indicator color="var(--bs-primary)" :throttle="0" />

    <div id="layout" :class="mainClasses">
      <app-navbar :class="navbarClasses" :sidebar="!isAdmin" />

      <div v-if="isColumns" class="main-background">
        <b-img :src="background" height="240" />
      </div>

      <b-container class="pt-4 flex-grow-1">
        <div v-if="isColumns">
          <b-row class="main-columns">
            <b-col md="8" :class="{offset: $isMobile}">
              <nuxt-page />
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
        </div>
        <slot v-else>
          <nuxt-page />
        </slot>
      </b-container>

      <app-sidebar v-if="$isMobile" :show-online="isColumns && showOnline" />
      <app-footer :class="footerClasses" />
    </div>
  </div>
</template>

<script setup lang="ts">
const {$settings, $image, $isMobile} = useNuxtApp()
const router = useRouter()
const route = useRoute()
const isColumns = computed(() => {
  return ['index', 'topics-uuid'].includes(router.currentRoute.value?.name as string)
})
const isAdmin = computed(() => {
  return router.currentRoute.value?.name && (router.currentRoute.value?.name as string).startsWith('admin')
})
const background = computed(() => {
  const bg = $settings.value.background
  return bg ? $image(bg, {h: 480, fit: 'crop-center'}) : ''
})
const showOnline = useRuntimeConfig().public.COMMENTS_SHOW_ONLINE === '1'

const mainClasses = computed(() => {
  const arr = ['d-flex', 'flex-column', 'min-vh-100']
  if (isColumns.value) {
    arr.push('bg-light')
  }
  if (route.name === 'index') {
    arr.push('main-page')
  }
  return arr
})
const navbarClasses = computed(() => {
  const arr = ['border-bottom']
  if (isColumns.value) {
    arr.push('bg-white')
  }
  return arr
})
const footerClasses = computed(() => {
  return ['border-top', isColumns.value ? 'bg-white' : 'bg-light']
})

function handleResize() {
  const width = process.client ? window.innerWidth : 768
  $isMobile.value = width < 768
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)

  // router.beforeEach(() => {
  //   clearError()
  // })
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>
