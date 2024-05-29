<template>
  <BNav class="pages" v-bind="props">
    <BNavItem v-for="link in links" :key="link.id" :to="{name: 'pages-alias', params: {alias: link.alias}}">
      {{ link.title }}
    </BNavItem>
  </BNav>
</template>

<script setup lang="ts">
import {BNav} from 'bootstrap-vue-next'

const props = defineProps({
  ...BNav.props,
  position: {
    type: String,
    default: 'header',
  },
})

const {$pages} = useNuxtApp()

const links = computed(() => {
  return $pages.value.filter((i: any) => ['both', props.position].includes(i.position))
})
</script>
