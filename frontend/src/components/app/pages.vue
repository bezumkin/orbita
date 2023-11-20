<template>
  <b-nav class="pages" v-bind="props">
    <b-nav-item v-for="link in links" :key="link.id" :to="{name: 'pages-alias', params: {alias: link.alias}}">
      {{ link.title }}
    </b-nav-item>
  </b-nav>
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
