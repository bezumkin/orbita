<template>
  <div>
    <NuxtLoadingIndicator color="var(--bs-primary)" />

    <div id="layout" :class="mainClasses">
      <AppNavbar class="border-bottom" :sidebar="!isAdmin" />

      <div v-if="isColumns" class="main-background">
        <BImg :src="background" height="240" />
      </div>

      <BContainer class="pt-4 flex-grow-1">
        <div v-if="isColumns">
          <BRow class="main-columns">
            <BCol md="8" :class="{offset: $isMobile}">
              <slot>
                <NuxtPage />
              </slot>
            </BCol>
            <BCol v-if="!$isMobile" md="4" class="offset">
              <div class="column">
                <WidgetsAuthor />
              </div>

              <WidgetsOnline v-if="showOnline" class="column mt-4" />
              <WidgetsLevels class="column mt-4" />
              <WidgetsTags class="column mt-4" />
            </BCol>
          </BRow>
        </div>
        <div v-else>
          <slot>
            <NuxtPage />
          </slot>
        </div>
      </BContainer>

      <AppSidebar v-if="$isMobile" :show-online="isColumns && showOnline" />
      <AppFooter class="border-top" />
      <AppPayment />
    </div>
  </div>
</template>

<script setup lang="ts">
import {stripTags} from '~/utils/vesp'

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

const description = stripTags(String($settings.value.description))
useSeoMeta({
  title: $settings.value.title as string,
  ogTitle: $settings.value.title as string,
  description,
  ogDescription: description,
  ogImage: $settings.value.poster ? $image($settings.value.poster as VespFile) : undefined,
  twitterCard: 'summary_large_image',
})
</script>
